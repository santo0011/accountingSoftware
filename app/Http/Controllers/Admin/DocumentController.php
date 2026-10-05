<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:documents.view')];
    }

    public function __construct(private DocumentService $documents) {}

    /** Review queue of customer uploads. */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $documents = ApplicationDocument::where('type', ApplicationDocument::TYPE_CUSTOMER)
            ->whereIn('application_id', Application::visibleTo($request->user())->select('id'))
            ->with('application:id,application_no,customer_id,service_id', 'application.customer.user:id,name', 'application.service:id,name')
            ->when($status === 'pending', fn ($q) => $q->whereIn('status', [DocumentStatus::Pending, DocumentStatus::UnderReview]))
            ->when($status !== 'pending' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('application', fn ($a) => $a->where('application_no', 'like', '%'.$request->q.'%')))
            ->latest()->paginate(per_page(25))->withQueryString();

        return view('admin.documents.index', compact('documents', 'status'));
    }

    public function view(ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('download', $document);

        return $this->documents->stream($document);
    }

    public function download(ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('download', $document);

        return $this->documents->download($document);
    }

    public function verify(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $this->authorize('review', $document);

        $this->documents->verify($document, $request->user());

        return back()->with('success', "\"{$document->name}\" verified.");
    }

    public function reject(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $this->authorize('review', $document);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'reupload' => ['nullable', 'boolean'],
        ]);

        $this->documents->reject($document, $data['reason'], $request->boolean('reupload', true), $request->user());

        return back()->with('success', "\"{$document->name}\" rejected — the customer has been notified.");
    }
}
