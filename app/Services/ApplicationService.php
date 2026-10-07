<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Business;
use App\Models\Customer;
use App\Models\DocumentRequest;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\AssignedToYou;
use App\Notifications\DocumentRequested;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/** Application lifecycle: submission, status changes, assignment, notes and document requests. */
class ApplicationService
{
    public function __construct(
        private NumberGenerator $numbers,
        private InvoiceService $invoices,
        private DocumentService $documents,
    ) {}

    /**
     * Create a submitted application with its uploaded documents and an unpaid invoice.
     *
     * @param array<string, mixed> $formData answers to the service's custom fields
     * @param array<int, UploadedFile> $files keyed by service_document id
     */
    public function submit(Customer $customer, Service $service, array $formData, array $files, ?Business $business, User $actor): Application
    {
        $application = DB::transaction(function () use ($customer, $service, $formData, $files, $business, $actor) {
            $quote = $this->invoices->quote($service, $this->invoices->placeOfSupply($customer, $business));
            $service->loadMissing('documents');

            $application = Application::create([
                'application_no' => $this->numbers->next('application'),
                'customer_id' => $customer->id,
                'business_id' => $business?->id,
                'service_id' => $service->id,
                'status' => ApplicationStatus::New,
                'payment_status' => $quote['total'] > 0 ? PaymentStatus::Pending : PaymentStatus::Paid,
                'form_data' => $formData,
                'amount' => $quote['amount'],
                'discount' => $quote['discount'],
                'tax' => $quote['tax'],
                'total' => $quote['total'],
                'submitted_at' => now(),
            ]);

            foreach ($service->documents as $required) {
                if (isset($files[$required->id])) {
                    $this->documents->store($application, $files[$required->id], $required->name, $actor, ApplicationDocument::TYPE_CUSTOMER, $required);
                }
            }

            $missing = $service->documents->where('is_mandatory', true)->reject(fn ($d) => isset($files[$d->id]));
            $initial = $missing->isNotEmpty() ? ApplicationStatus::DocumentsPending : ApplicationStatus::New;
            $application->update(['status' => $initial]);

            $application->histories()->create([
                'from_status' => null, 'to_status' => $initial, 'changed_by' => $actor->id,
                'remarks' => $missing->isNotEmpty() ? 'Submitted; pending documents: '.$missing->pluck('name')->implode(', ') : 'Application submitted',
            ]);

            if ($quote['total'] > 0) {
                $this->invoices->createForApplication($application);
            }

            return $application;
        });

        $customer->user->notify(new ApplicationSubmitted($application));
        Notification::send(User::role(config('rbac.super_admin_role'))->where('status', 'active')->get(), new ApplicationSubmitted($application));

        return $application;
    }

    public function changeStatus(Application $application, ApplicationStatus $to, ?User $actor, ?string $remarks = null, bool $notify = true): void
    {
        $from = $application->status;
        if ($from === $to) {
            return;
        }

        DB::transaction(function () use ($application, $from, $to, $actor, $remarks) {
            $application->update([
                'status' => $to,
                'completed_at' => $to === ApplicationStatus::Completed ? now() : $application->completed_at,
            ]);

            $application->histories()->create([
                'from_status' => $from, 'to_status' => $to, 'changed_by' => $actor?->id, 'remarks' => $remarks,
            ]);

            if ($to === ApplicationStatus::Completed) {
                app(ComplianceService::class)->activateFromApplication($application);
            }
        });

        if ($notify) {
            $application->customer->user->notify(new ApplicationStatusChanged($application, $remarks));
        }
    }

    public function assign(Application $application, ?int $staffId, ?int $professionalId, User $actor): void
    {
        $previousStaff = $application->assigned_staff_id;
        $previousProfessional = $application->assigned_professional_id;

        $application->update(['assigned_staff_id' => $staffId, 'assigned_professional_id' => $professionalId]);

        $link = route('admin.applications.show', $application);
        $what = "Application {$application->application_no} ({$application->service->name})";

        if ($staffId && $staffId !== $previousStaff && $staffId !== $actor->id) {
            User::find($staffId)?->notify(new AssignedToYou($what, $link));
        }
        if ($professionalId && $professionalId !== $previousProfessional) {
            Professional::find($professionalId)?->user?->notify(new AssignedToYou($what, $link));
        }
    }

    public function addNote(Application $application, string $note, bool $internal, User $author): void
    {
        $application->notes()->create(['note' => $note, 'is_internal' => $internal, 'user_id' => $author->id]);
    }

    public function requestDocument(Application $application, string $documentName, ?string $note, User $actor): DocumentRequest
    {
        $request = DB::transaction(function () use ($application, $documentName, $note, $actor) {
            $request = $application->documentRequests()->create([
                'document_name' => $documentName, 'note' => $note, 'requested_by' => $actor->id,
            ]);

            if (! $application->status->isClosed()) {
                $this->changeStatus($application, ApplicationStatus::DocumentsPending, $actor, "Additional document requested: {$documentName}", notify: false);
            }

            return $request;
        });

        $application->customer->user->notify(new DocumentRequested($request));

        return $request;
    }

    /** Customer cancels an application that has not been worked on yet. */
    public function cancelByCustomer(Application $application, User $customerUser): void
    {
        $this->changeStatus($application, ApplicationStatus::Cancelled, $customerUser, 'Cancelled by customer');
        $application->invoice?->update(['status' => \App\Enums\InvoiceStatus::Cancelled]);
    }
}
