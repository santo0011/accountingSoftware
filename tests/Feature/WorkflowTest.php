<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use App\Services\ComplianceService;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['payments.gateways.sandbox.enabled' => true]);
        $this->seed(DatabaseSeeder::class);
    }

    public function test_customer_can_register_and_login_with_mobile(): void
    {
        $this->post(route('register'), [
            'name' => 'Test Founder', 'email' => 'founder@example.com', 'mobile' => '9123456789',
            'business_name' => 'Founder Labs', 'business_type' => 'startup',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123', 'terms' => '1',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'founder@example.com')->first();
        $this->assertTrue($user->isCustomer());
        $this->assertNotNull($user->customer->customer_code);
        $this->assertSame('Founder Labs', $user->customer->businesses->first()->name);

        auth()->logout();
        $this->post(route('login'), ['login' => '9123456789', 'password' => 'Secret123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_full_application_lifecycle(): void
    {
        $customerUser = User::where('email', 'customer@bizsetu.test')->first();
        $service = Service::where('slug', 'gst-return-filing')->with('documents', 'fields')->first();
        $business = $customerUser->customer->businesses->first();

        // 1. Apply with a document.
        $firstDoc = $service->documents->first();
        $fields = $service->fields->mapWithKeys(fn ($f) => [$f->name => $f->type === 'select' ? $f->options[0] : 'Test value'])->all();

        $response = $this->actingAs($customerUser)->post(route('portal.applications.store', $service->slug), [
            'business_id' => $business->id,
            'fields' => $fields,
            'documents' => [$firstDoc->id => UploadedFile::fake()->create('sales.pdf', 120, 'application/pdf')],
            'confirm' => '1',
        ]);

        $application = Application::latest('id')->first();
        $response->assertRedirect(route('portal.payments.checkout', $application));
        $this->assertMatchesRegularExpression('/^APP-\d{4}-\d{6}$/', $application->application_no);
        $this->assertSame(ApplicationStatus::DocumentsPending, $application->status); // other mandatory docs missing
        $this->assertNotNull($application->invoice);
        $this->assertEquals(round($service->effectivePrice() * 1.18, 2), (float) $application->total);
        $this->assertEquals((float) $application->invoice->cgst, (float) $application->invoice->sgst); // same state
        Storage::disk('local')->assertExists($application->documents->first()->path);

        // 2. Pay with the sandbox gateway.
        $this->post(route('portal.payments.pay', $application), ['gateway' => 'sandbox'])
            ->assertRedirect(route('portal.applications.show', $application));
        $application->refresh();
        $this->assertSame(PaymentStatus::Paid, $application->payment_status);
        $this->assertSame('paid', $application->invoice->status->value);

        // 3. Admin rejects the document and requests re-upload.
        $admin = User::where('email', 'admin@bizsetu.test')->first();
        $doc = $application->documents->first();
        $this->actingAs($admin)->post(route('admin.documents.reject', $doc), ['reason' => 'Blurry scan', 'reupload' => '1'])->assertRedirect();
        $this->assertSame(DocumentStatus::ReuploadRequired, $doc->fresh()->status);

        // 4. Customer re-uploads; admin verifies.
        $this->actingAs($customerUser)->post(route('portal.applications.documents.store', $application), [
            'replaces_id' => $doc->id, 'file' => UploadedFile::fake()->create('sales-v2.pdf', 80, 'application/pdf'),
        ])->assertRedirect();
        $new = $application->documents()->latest('id')->first();
        $this->actingAs($admin)->post(route('admin.documents.verify', $new))->assertRedirect();
        $this->assertSame(DocumentStatus::Verified, $new->fresh()->status);

        // 5. Admin completes the application -> recurring compliance is created.
        $this->post(route('admin.applications.status', $application), ['status' => 'completed', 'remarks' => 'Filed', 'notify' => '1'])->assertRedirect();
        $application->refresh();
        $this->assertSame(ApplicationStatus::Completed, $application->status);
        $this->assertNotNull($application->customerService);
        $record = $application->customerService->complianceRecords()->first();
        $this->assertNotNull($record);
        $this->assertSame(20, $record->due_date->day); // GSTR-3B due on the 20th

        // 6. Completing the compliance record generates the next period.
        $this->post(route('admin.compliance.complete', $record))->assertRedirect();
        $this->assertSame(2, $application->customerService->complianceRecords()->count());

        // Customer received notifications along the way.
        $this->assertGreaterThan(3, $customerUser->notifications()->count());
    }

    public function test_manual_payment_waits_for_verification(): void
    {
        $customerUser = User::where('email', 'customer@bizsetu.test')->first();
        $service = Service::where('slug', 'trademark-registration')->first();

        $this->actingAs($customerUser)->post(route('portal.applications.store', $service->slug), [
            'new_business_name' => 'New Brand Co', 'fields' => ['brand_name' => 'ZENTRO', 'in_use' => 'Yes', 'goods' => 'Apparel'], 'confirm' => '1',
        ]);
        $application = Application::latest('id')->first();

        $this->post(route('portal.payments.pay', $application), ['gateway' => 'manual', 'method' => 'upi', 'reference' => 'UPI123456789'])
            ->assertRedirect(route('portal.payments.index'));
        $payment = $application->payments()->first();
        $this->assertSame(PaymentStatus::Pending, $payment->status);

        $accountant = User::where('email', 'accounts@bizsetu.test')->first();
        $this->actingAs($accountant)->post(route('admin.payments.confirm', $payment))->assertRedirect();
        $this->assertSame(PaymentStatus::Paid, $application->fresh()->payment_status);
    }

    public function test_contact_form_creates_lead_and_lead_converts_to_customer(): void
    {
        $this->post(route('site.contact.store'), [
            'name' => 'Ravi Kumar', 'phone' => '9988776655', 'email' => 'ravi@example.com',
            'message' => 'Need GST registration', 'service_id' => Service::where('slug', 'gst-registration')->value('id'),
        ])->assertRedirect()->assertSessionHas('success');

        $lead = Lead::where('email', 'ravi@example.com')->first();
        $this->assertSame('contact_form', $lead->source);

        $this->actingAs(User::where('email', 'admin@bizsetu.test')->first())
            ->post(route('admin.leads.convert', $lead))->assertRedirect();
        $this->assertNotNull($lead->fresh()->customer_id);
        $this->assertTrue(User::where('email', 'ravi@example.com')->first()->isCustomer());
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post(route('site.contact.store'), [
            'name' => 'Bot', 'phone' => '9988776655', 'email' => 'bot@example.com', 'message' => 'spam', 'website' => 'http://spam',
        ])->assertSessionHasErrors('website');
        $this->assertDatabaseMissing('leads', ['email' => 'bot@example.com']);
    }

    public function test_upload_rejects_dangerous_files(): void
    {
        $customerUser = User::where('email', 'customer@bizsetu.test')->first();
        $service = Service::where('slug', 'gst-registration')->with('documents')->first();

        $this->actingAs($customerUser)->post(route('portal.applications.store', $service->slug), [
            'new_business_name' => 'X', 'confirm' => '1',
            'fields' => ['trade_name' => 'X', 'state' => 'Maharashtra', 'turnover' => 'Below ₹20 lakh'],
            'documents' => [$service->documents->first()->id => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
        ])->assertSessionHasErrors('documents.'.$service->documents->first()->id);
    }

    public function test_compliance_periods_and_due_dates(): void
    {
        $svc = app(ComplianceService::class);

        [$start, $end, $label] = $svc->period('quarterly', CarbonImmutable::parse('2026-08-10'));
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-09-30', $end->toDateString());
        $this->assertSame('Q2 FY 2026-27', $label);

        [, , $label] = $svc->period('yearly', CarbonImmutable::parse('2027-02-01'));
        $this->assertSame('FY 2026-27', $label);
    }

    public function test_money_formats_in_indian_style(): void
    {
        $this->assertSame('₹1,25,000.00', money(125000));
        $this->assertSame('₹12,34,567', money(1234567, false));
        $this->assertSame('₹999.50', money(999.5));
    }
}
