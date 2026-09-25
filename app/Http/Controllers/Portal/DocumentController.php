<?php

namespace App\Http\Controllers\Portal;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    public function index(Request $request): View
    {
        $applicationIds = $request->user()->customer->applications()->select('id');

        $documents = ApplicationDocument::whereIn('application_id', $applicationIds)
            ->with('application:id,application_no,service_id', 'application.service:id,name')
            ->when($request->query('type') === 'deliverable', fn ($q) => $q->where('type', ApplicationDocument::TYPE_DELIVERABLE))
            ->when($request->query('type') === 'uploaded', fn ($q) => $q->where('type', ApplicationDocument::TYPE_CUSTOMER))
            ->latest()->paginate(15)->withQueryString();

        $attention = ApplicationDocument::whereIn('application_id', $applicationIds)
            ->where('status', DocumentStatus::ReuploadRequired)->with('application:id,application_no')->get();

        return view('portal.documents.index', compact('documents', 'attention'));
    }

    /** Upload a missing, requested or replacement document for an application. */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('upload', $application);

        $data = $request->validate([
            'file' => DocumentService::fileRules(),
            'document_request_id' => ['nullable', Rule::exists('document_requests', 'id')->where('application_id', $application->id)->whereNull('fulfilled_at')],
            'replaces_id' => ['nullable', Rule::exists('application_documents', 'id')->where('application_id', $application->id)->where('type', 'customer')],
            'service_document_id' => ['nullable', Rule::exists('service_documents', 'id')->where('service_id', $application->service_id)],
            'name' => ['nullable', 'string', 'max:150'],
        ]);

        $docRequest = isset($data['document_request_id']) ? $application->documentRequests()->find($data['document_request_id']) : null;
        $replaces = isset($data['replaces_id']) ? $application->documents()->find($data['replaces_id']) : null;
        $serviceDocument = isset($data['service_document_id']) ? $application->service->documents()->find($data['service_document_id']) : null;

        $name = $docRequest?->document_name ?? $replaces?->name ?? $serviceDocument?->name ?? ($data['name'] ?? null) ?? 'Additional document';

        $document = $this->documents->customerUpload($application, $request->file('file'), $name, $request->user(), $docRequest, $replaces);
        if ($serviceDocument && ! $document->service_document_id) {
            $document->update(['service_document_id' => $serviceDocument->id]);
        }

        return back()->with('success', "\"{$name}\" uploaded successfully. Our team will review it shortly.");
    }

    public function download(ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('download', $document);

        return $this->documents->download($document);
    }
}
