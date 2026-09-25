<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\LeadStatus;
use App\Enums\TicketStatus;
use App\Models\Application;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use App\Services\ApplicationService;
use App\Services\ComplianceService;
use App\Services\CustomerAccountService;
use App\Services\LeadService;
use App\Services\PaymentService;
use App\Services\TicketService;
use Illuminate\Database\Seeder;

/** Realistic sample data so every dashboard, list and chart has something to show. */
class DemoDataSeeder extends Seeder
{
    public function run(
        CustomerAccountService $accounts,
        ApplicationService $applications,
        PaymentService $payments,
        ComplianceService $compliance,
        LeadService $leads,
        TicketService $tickets,
    ): void {
        config(['mail.default' => 'array']); // keep in-app notifications, skip emails

        if (Application::exists()) {
            return; // already seeded
        }

        $admin = User::where('email', UserSeeder::ADMIN_EMAIL)->first();
        $staff = User::role('staff')->get();
        $professionals = Professional::all();

        $people = [
            ['Aarav Patel', 'customer@bizsetu.test', null, null, 'Maharashtra', 'Mumbai'],
            ['Ishita Sharma', 'ishita@example.com', 'Sharma Organics LLP', 'llp', 'Delhi', 'New Delhi'],
            ['Karan Malhotra', 'karan@example.com', 'Malhotra Traders', 'proprietorship', 'Punjab', 'Ludhiana'],
            ['Divya Menon', 'divya@example.com', 'Menon Foods Pvt Ltd', 'private_limited', 'Kerala', 'Kochi'],
            ['Rajesh Gupta', 'rajesh@example.com', 'Gupta Exports', 'partnership', 'Gujarat', 'Surat'],
            ['Sana Khan', 'sana@example.com', 'Khan Design Studio', 'proprietorship', 'Maharashtra', 'Pune'],
            ['Manoj Yadav', 'manoj@example.com', 'Yadav Logistics Pvt Ltd', 'private_limited', 'Karnataka', 'Bengaluru'],
            ['Ananya Bose', 'ananya@example.com', 'Bose Foundation', 'section8', 'West Bengal', 'Kolkata'],
        ];

        $customers = [];
        foreach ($people as $i => [$name, $email, $business, $type, $state, $city]) {
            $customers[] = User::where('email', $email)->first()?->customer ?? $accounts->create([
                'name' => $name, 'email' => $email, 'mobile' => '98765'.str_pad((string) ($i + 10), 5, '0', STR_PAD_LEFT),
                'password' => UserSeeder::DEFAULT_PASSWORD, 'business_name' => $business, 'business_type' => $type,
                'state' => $state, 'city' => $city, 'source' => 'website',
            ], verified: true);
        }

        // [customer index, service slug, final status, paid?, months ago]
        $plan = [
            [0, 'private-limited-company-registration', ApplicationStatus::Completed, true, 10],
            [0, 'gst-return-filing', ApplicationStatus::Completed, true, 8],
            [0, 'trademark-registration', ApplicationStatus::Filed, true, 2],
            [0, 'annual-compliance-package', ApplicationStatus::Processing, true, 1],
            [1, 'llp-registration', ApplicationStatus::Completed, true, 9],
            [1, 'fssai-registration', ApplicationStatus::InProgress, true, 1],
            [2, 'gst-registration', ApplicationStatus::Completed, true, 7],
            [2, 'tds-return-filing', ApplicationStatus::Completed, true, 6],
            [3, 'fssai-registration', ApplicationStatus::Completed, true, 6],
            [3, 'online-bookkeeping', ApplicationStatus::Completed, true, 5],
            [3, 'tds-return-filing', ApplicationStatus::Completed, true, 4],
            [4, 'import-export-code', ApplicationStatus::Completed, true, 5],
            [4, 'gst-lut-filing', ApplicationStatus::DocumentsVerified, true, 0],
            [5, 'logo-design', ApplicationStatus::Completed, true, 4],
            [5, 'website-development', ApplicationStatus::UnderReview, true, 0],
            [6, 'iso-certification', ApplicationStatus::DocumentsPending, false, 0],
            [6, 'trademark-registration', ApplicationStatus::New, false, 0],
            [7, '12a-registration', ApplicationStatus::Processing, true, 3],
            [7, '80g-registration', ApplicationStatus::PaymentPending, false, 0],
            [2, 'tax-planning-consultancy', ApplicationStatus::Cancelled, false, 3],
            [1, 'trademark-objection-reply', ApplicationStatus::Rejected, false, 2],
        ];

        $flow = [ApplicationStatus::UnderReview, ApplicationStatus::DocumentsVerified, ApplicationStatus::Processing, ApplicationStatus::Filed, ApplicationStatus::InProgress, ApplicationStatus::Completed];

        foreach ($plan as $n => [$ci, $slug, $final, $paid, $monthsAgo]) {
            $customer = $customers[$ci]->fresh(['user', 'businesses']);
            $service = Service::where('slug', $slug)->firstOrFail();
            $date = now()->subMonths($monthsAgo)->subDays(($n * 3) % 20);

            $app = $applications->submit($customer, $service, ['notes' => 'Sample application'], [], $customer->businesses->first(), $customer->user);
            $app->update(['created_at' => $date, 'submitted_at' => $date]);
            $app->histories()->update(['created_at' => $date]);
            $app->invoice?->update(['invoice_date' => $date, 'created_at' => $date]);

            $assignee = $staff[$n % max($staff->count(), 1)] ?? $admin;
            $applications->assign($app, $assignee->id, $professionals[$n % $professionals->count()]->id, $admin);

            if ($paid && $app->total > 0) {
                $payment = $payments->recordOffline($app, (float) $app->total, $n % 2 ? 'upi' : 'bank_transfer', 'UTR'.(880000 + $n * 137), $admin);
                $payment->update(['paid_at' => $date->copy()->addDay(), 'created_at' => $date->copy()->addDay()]);
            }

            if (in_array($final, $flow, true)) {
                foreach ($flow as $step) {
                    $applications->changeStatus($app->fresh(), $step, $assignee, null, notify: $step === $final);
                    if ($step === $final) {
                        break;
                    }
                }
            } elseif ($final !== ApplicationStatus::New) {
                $applications->changeStatus($app->fresh(), $final, $assignee, $final === ApplicationStatus::Rejected ? 'Mark is identical to an earlier registered trademark.' : null);
            }

            if ($final === ApplicationStatus::DocumentsPending) {
                $applications->requestDocument($app->fresh(), 'Quality manual / SOP document', 'Please upload your latest quality manual for the ISO audit.', $assignee);
            }

            if ($final === ApplicationStatus::Completed) {
                $app->update(['completed_at' => $date->copy()->addDays(10)]);
            }

            $applications->addNote($app, 'Called the customer and confirmed requirements.', true, $assignee);
        }

        // Pending manual payment awaiting verification.
        $pending = Application::where('status', ApplicationStatus::PaymentPending)->first();
        if ($pending) {
            $payments->checkout($pending, 'manual', ['method' => 'upi', 'reference' => 'UPI43210987'], $pending->customer->user);
        }

        $compliance->refreshStatuses();

        $leadRows = [
            ['Nikhil Rao', 'nikhil@example.com', '9811100001', 'Rao Infra', 'private-limited-company-registration', 'website', LeadStatus::New],
            ['Pallavi Joshi', 'pallavi@example.com', '9811100002', 'PJ Boutique', 'gst-registration', 'contact_form', LeadStatus::Contacted],
            ['Harsh Vardhan', 'harsh@example.com', '9811100003', null, 'trademark-registration', 'google_ads', LeadStatus::FollowUp],
            ['Zoya Ali', 'zoya@example.com', '9811100004', 'Zoya Bakes', 'fssai-registration', 'social', LeadStatus::New],
            ['Deepak Singh', 'deepak@example.com', '9811100005', 'Singh Motors', 'online-bookkeeping', 'referral', LeadStatus::FollowUp],
            ['Lata Pillai', 'lata@example.com', '9811100006', 'Pillai Spices', 'import-export-code', 'phone', LeadStatus::NotInterested],
            ['Gaurav Jain', 'gaurav@example.com', '9811100007', 'Jain Tech', 'website-development', 'website', LeadStatus::New],
            ['Mitali Das', 'mitali@example.com', '9811100008', 'Das Care Trust', '12a-registration', 'whatsapp', LeadStatus::Contacted],
            ['Omkar Patil', 'omkar@example.com', '9811100009', 'Patil Agro', 'fssai-registration', 'website', LeadStatus::Closed],
            ['Tanvi Shah', 'tanvi@example.com', '9811100010', 'Shah & Co', 'annual-compliance-package', 'referral', LeadStatus::New],
        ];
        foreach ($leadRows as $i => [$name, $email, $phone, $company, $slug, $source, $status]) {
            $lead = $leads->create([
                'name' => $name, 'email' => $email, 'phone' => $phone, 'company' => $company,
                'service_id' => Service::where('slug', $slug)->value('id'), 'source' => $source,
                'assigned_to' => $staff[$i % max($staff->count(), 1)]->id ?? null,
                'message' => 'I would like to know the process and fees.',
            ], $admin);
            $lead->update(['status' => $status, 'created_at' => now()->subDays($i * 4)]);

            if ($status === LeadStatus::FollowUp) {
                $leads->addFollowup($lead, [
                    'channel' => 'call', 'remarks' => 'Discussed pricing, customer will confirm this week.',
                    'status' => LeadStatus::FollowUp, 'next_followup_at' => now()->addDays(2)->toDateString(),
                ], $lead->assignee ?? $admin);
            }
        }

        $ticketCustomer = $customers[0]->fresh('user');
        $ticket = $tickets->open($ticketCustomer, [
            'subject' => 'When will I receive my trademark certificate?',
            'category' => 'application', 'priority' => 'medium',
            'application_id' => $ticketCustomer->applications()->whereHas('service', fn ($q) => $q->where('slug', 'trademark-registration'))->value('id'),
            'message' => 'Hi, my trademark application was filed. What are the next steps and expected timeline?',
        ], null, $ticketCustomer->user);
        $tickets->reply($ticket, $staff->first() ?? $admin, 'Your application is filed and has a TM number. Examination usually takes 3–6 months; we will update you as soon as the examination report is issued.', null);

        $tickets->open($customers[3]->fresh('user'), [
            'subject' => 'Need GST invoice with updated address',
            'category' => 'payment', 'priority' => 'low',
            'message' => 'Please update the billing address on my last invoice to our new office.',
        ], null, $customers[3]->user);

        $closed = $tickets->open($customers[1]->fresh('user'), [
            'subject' => 'Unable to upload PDF', 'category' => 'technical', 'priority' => 'high',
            'message' => 'The upload button was not working yesterday.',
        ], null, $customers[1]->user);
        $tickets->updateStatus($closed, TicketStatus::Resolved, $staff->first()?->id, $admin);
    }
}
