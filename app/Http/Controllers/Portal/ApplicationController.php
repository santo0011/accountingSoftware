<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Business;
use App\Models\Service;
use App\Services\ApplicationService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $applications) {}

    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $applications = $request->user()->customer->applications()
            ->with('service:id,name,icon,service_category_id', 'service.category:id,icon')
            ->when($filter === 'active', fn ($q) => $q->active())
            ->when($filter === 'completed', fn ($q) => $q->where('status', ApplicationStatus::Completed))
            ->when($filter === 'closed', fn ($q) => $q->whereIn('status', [ApplicationStatus::Rejected, ApplicationStatus::Cancelled]))
            ->latest()->paginate(10)->withQueryString();

        return view('portal.applications.index', compact('applications', 'filter'));
    }

    public function create(Request $request, Service $service, InvoiceService $invoices): View
    {
        abort_unless($service->status, 404);

        $customer = $request->user()->customer;
        $service->load('fields', 'documents', 'category');
        $businesses = $customer->businesses()->orderByDesc('is_primary')->get();
        $quote = $invoices->quote($service, $businesses->first()?->state ?? $customer->state);

        return view('portal.applications.create', compact('service', 'businesses', 'quote'));
    }

    public function store(StoreApplicationRequest $request, Service $service): RedirectResponse
    {
        $customer = $request->user()->customer;

        $business = $request->filled('business_id')
            ? $customer->businesses()->findOrFail($request->integer('business_id'))
            : $customer->businesses()->create([
                'name' => $request->string('new_business_name'),
                'business_type' => $request->input('new_business_type'),
                'state' => $request->input('new_business_state') ?: $customer->state,
                'is_primary' => ! $customer->businesses()->exists(),
            ]);

        $files = array_filter((array) $request->file('documents', []));

        $application = $this->applications->submit($customer, $service, $request->input('fields', []), $files, $business, $request->user());

        if ($request->user()->can('pay', $application)) {
            return redirect()->route('portal.payments.checkout', $application)
                ->with('success', "Application {$application->application_no} submitted. Complete the payment to start processing.");
        }

        return redirect()->route('portal.applications.show', $application)
            ->with('success', "Application {$application->application_no} submitted successfully.");
    }

    public function show(Request $request, Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'service.documents', 'service.fields', 'business', 'staff:id,name', 'professional:id,name,professional_type', 'invoice',
            'histories', 'payments', 'documentRequests' => fn ($q) => $q->open(),
            'documents' => fn ($q) => $q->with('serviceDocument:id,name'),
            'notes' => fn ($q) => $q->where('is_internal', false)->with('user:id,name,user_type'),
        ]);

        $uploadedIds = $application->documents->where('type', 'customer')->pluck('service_document_id')->filter()->all();
        $missing = $application->service->documents->reject(fn ($d) => in_array($d->id, $uploadedIds, true));

        return view('portal.applications.show', compact('application', 'missing'));
    }

    public function cancel(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('cancel', $application);

        $this->applications->cancelByCustomer($application, $request->user());

        return redirect()->route('portal.applications.show', $application)->with('success', 'Your application has been cancelled.');
    }
}
