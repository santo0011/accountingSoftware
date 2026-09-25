<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ComplianceRecord;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SupportTicket;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Renders every page of the site, portal and admin panel with realistic demo data. */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoDataSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $service = Service::first();
        $urls = [
            route('site.home'), route('site.services.index'), route('site.services.index', ['q' => 'gst']),
            route('site.categories.show', ServiceCategory::first()->slug), route('site.services.show', $service->slug),
            route('site.about'), route('site.pricing'), route('site.faq'), route('site.contact'),
            route('site.privacy'), route('site.terms'), route('site.refund'), route('site.sitemap'),
            route('login'), route('register'), route('password.request'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        $this->getJson(route('site.services.search', ['q' => 'trade']))->assertOk()->assertJsonStructure(['data' => [['name', 'url']]]);
    }

    public function test_customer_portal_pages_render(): void
    {
        $user = User::where('email', 'customer@bizsetu.test')->first();
        $application = $user->customer->applications()->first();
        $ticket = $user->customer->tickets()->first();
        $unpaid = Service::where('slug', 'gst-registration')->first();

        $this->actingAs($user);
        foreach ([
            route('portal.dashboard'), route('portal.services.index'), route('portal.applications.index'),
            route('portal.applications.create', $unpaid->slug), route('portal.applications.show', $application),
            route('portal.documents.index'), route('portal.payments.index'), route('portal.invoices.index'),
            route('portal.compliance.index'), route('portal.notifications.index'), route('portal.support.index'),
            route('portal.support.create'), route('portal.support.show', $ticket), route('portal.profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('portal.invoices.pdf', $user->customer->invoices()->first()))->assertOk();
        $this->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
    }

    public function test_admin_pages_render_for_super_admin(): void
    {
        $this->actingAs(User::where('email', 'admin@bizsetu.test')->first());

        $application = Application::first();
        foreach ([
            route('admin.dashboard'), route('admin.search', ['q' => 'Patel']),
            route('admin.customers.index'), route('admin.customers.create'), route('admin.customers.show', Customer::first()), route('admin.customers.edit', Customer::first()),
            route('admin.leads.index'), route('admin.leads.create'), route('admin.leads.show', Lead::first()), route('admin.leads.edit', Lead::first()),
            route('admin.categories.index'), route('admin.categories.create'), route('admin.categories.edit', ServiceCategory::first()),
            route('admin.services.index'), route('admin.services.create'), route('admin.services.edit', Service::first()),
            route('admin.applications.index'), route('admin.applications.create'), route('admin.applications.show', $application),
            route('admin.documents.index'), route('admin.payments.index'), route('admin.invoices.index'), route('admin.invoices.show', Invoice::first()),
            route('admin.staff.index'), route('admin.staff.create'), route('admin.staff.edit', User::role('staff')->first()),
            route('admin.professionals.index'), route('admin.professionals.create'), route('admin.professionals.edit', Professional::first()),
            route('admin.roles.index'), route('admin.roles.create'), route('admin.roles.edit', Role::findByName('staff')),
            route('admin.tasks.index'), route('admin.tasks.index', ['scope' => 'all']), route('admin.tasks.create'), route('admin.tasks.edit', Task::first()),
            route('admin.compliance.index'), route('admin.compliance.create'), route('admin.compliance.edit', ComplianceRecord::first()), route('admin.compliance-types.index'),
            route('admin.support.index'), route('admin.support.show', SupportTicket::first()),
            route('admin.reports.index'), route('admin.notifications.index'),
            route('admin.pages.index'), route('admin.pages.edit', Page::first()), route('admin.faqs.index'), route('admin.faqs.create'),
            route('admin.testimonials.index'), route('admin.testimonials.create'),
            route('admin.settings.edit'), route('admin.audit.index'), route('admin.profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('admin.invoices.pdf', Invoice::first()))->assertOk();
        $this->get(route('admin.reports.export', 'applications'))->assertOk();
    }

    public function test_staff_sees_only_permitted_areas(): void
    {
        $staff = User::where('email', 'rahul.staff@bizsetu.test')->first();
        $this->actingAs($staff);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.applications.index'))->assertOk();
        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.roles.index'))->assertForbidden();

        $notAssigned = Application::where('assigned_staff_id', '!=', $staff->id)->first();
        $this->get(route('admin.applications.show', $notAssigned))->assertForbidden();
        $this->get(route('portal.dashboard'))->assertRedirect(route('dashboard'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_another_customers_records(): void
    {
        $customer = User::where('email', 'customer@bizsetu.test')->first();
        $other = Customer::where('id', '!=', $customer->customer->id)->has('applications')->first();
        $otherApp = $other->applications()->first();
        $doc = ApplicationDocument::create([
            'application_id' => $otherApp->id, 'type' => 'customer', 'name' => 'PAN', 'original_name' => 'pan.pdf',
            'disk' => 'local', 'path' => 'applications/x/pan.pdf', 'mime' => 'application/pdf', 'size' => 10, 'status' => 'pending',
        ]);

        $this->actingAs($customer);
        $this->get(route('portal.applications.show', $otherApp))->assertForbidden();
        $this->get(route('portal.documents.download', $doc))->assertForbidden();
        $this->get(route('portal.invoices.pdf', $other->invoices()->first()))->assertForbidden();
    }
}
