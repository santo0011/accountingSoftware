<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentRequest;
use App\Models\ServiceDocument;
use App\Models\User;
use App\Support\FileTypes;
use App\Notifications\DocumentReviewed;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Stores application documents on the private disk (never publicly reachable)
 * and handles the verify / reject / re-upload workflow.
 */
class DocumentService
{
    public const DISK = 'local';
    public const MAX_KB = 5120;

    /** Validation rules for any uploaded document. */
    public static function fileRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'file', FileTypes::rule(FileTypes::DOCUMENTS), 'max:'.self::MAX_KB];
    }

    public function store(
        Application $application,
        UploadedFile $file,
        string $name,
        User $uploader,
        string $type = ApplicationDocument::TYPE_CUSTOMER,
        ?ServiceDocument $serviceDocument = null,
        ?DocumentRequest $request = null,
    ): ApplicationDocument {
        $path = $file->storeAs(
            "applications/{$application->id}",
            Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension()),
            self::DISK,
        );

        $document = $application->documents()->create([
            'service_document_id' => $serviceDocument?->id,
            'uploaded_by' => $uploader->id,
            'type' => $type,
            'name' => $name,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'disk' => self::DISK,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            // Final deliverables uploaded by staff need no review.
            'status' => $type === ApplicationDocument::TYPE_DELIVERABLE ? DocumentStatus::Verified : DocumentStatus::Pending,
        ]);

        $request?->update(['fulfilled_at' => now(), 'application_document_id' => $document->id]);

        return $document;
    }

    /** Customer re-upload / additional upload after submission. */
    public function customerUpload(Application $application, UploadedFile $file, string $name, User $uploader, ?DocumentRequest $request = null, ?ApplicationDocument $replaces = null): ApplicationDocument
    {
        return DB::transaction(function () use ($application, $file, $name, $uploader, $request, $replaces) {
            $document = $this->store($application, $file, $name, $uploader, ApplicationDocument::TYPE_CUSTOMER, $replaces?->serviceDocument, $request);

            // The replaced file stays for audit, but is no longer awaiting action.
            $replaces?->update(['status' => DocumentStatus::Rejected]);

            if ($application->status === ApplicationStatus::DocumentsPending && ! $application->documentRequests()->open()->exists()
                && ! $application->documents()->where('type', ApplicationDocument::TYPE_CUSTOMER)->where('status', DocumentStatus::ReuploadRequired)->exists()) {
                app(ApplicationService::class)->changeStatus($application, ApplicationStatus::UnderReview, $uploader, 'Customer uploaded the requested documents.', notify: false);
            }

            return $document;
        });
    }

    public function verify(ApplicationDocument $document, User $reviewer): void
    {
        $document->update([
            'status' => DocumentStatus::Verified, 'rejection_reason' => null,
            'reviewed_by' => $reviewer->id, 'reviewed_at' => now(),
        ]);

        activity('documents')->performedOn($document->application)->causedBy($reviewer)->log("Verified document \"{$document->name}\"");
        $document->application->customer->user->notify(new DocumentReviewed($document));
    }

    public function reject(ApplicationDocument $document, string $reason, bool $requestReupload, User $reviewer): void
    {
        $document->update([
            'status' => $requestReupload ? DocumentStatus::ReuploadRequired : DocumentStatus::Rejected,
            'rejection_reason' => $reason, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(),
        ]);

        $application = $document->application;
        if ($requestReupload && ! $application->status->isClosed()) {
            app(ApplicationService::class)->changeStatus($application, ApplicationStatus::DocumentsPending, $reviewer, "Re-upload requested: {$document->name}", notify: false);
        }

        activity('documents')->performedOn($application)->causedBy($reviewer)->log("Rejected document \"{$document->name}\": {$reason}");
        $application->customer->user->notify(new DocumentReviewed($document));
    }

    public function download(ApplicationDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404, 'File not found.');

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    public function stream(ApplicationDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404, 'File not found.');

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
        ]);
    }
}
