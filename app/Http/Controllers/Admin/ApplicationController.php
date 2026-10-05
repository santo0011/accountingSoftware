<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use App\Services\ApplicationService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ApplicationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:applications.view', only: ['index', 'show']),
            new Middleware('permission:applications.create', only: ['create', 'store']),
        ];
    }

    public function __construct(private ApplicationService $applications, private DocumentService $documents) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $applications = Application::visibleTo($user)
            ->with('customer.user:id,name,mobile', 'service:id,name', 'staff:id,name')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('application_no', 'like', $term)
                    ->orWhereHas('customer.user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term)));
            })
            ->when($request->filled('status'), fn ($q) => $request->status === 'active' ? $q->active() : $q->where('status', $request->status))
            ->when($request->filled('payment'), fn ($q) => $q->where('payment_status', $request->payment))
            ->when($request->filled('service'), fn ($q) => $q->where('service_id', $request->service))
            ->when($request->filled('staff'), fn ($q) => $request->staff === 'unassigned' ? $q->whereNull('assigned_staff_id') : $q->where('assigned_staff_id', $request->staff))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()->paginate(per_page(20))->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::options(),
            'services' => Service::orderBy('name')->pluck('name', 'id'),
            'staff' => User::backoffice()->where('user_type', User::TYPE_STAFF)->orderBy('name')->pluck('name', 'id'),
            'counts' => Application::visibleTo($user)->toBase()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.applications.create', [
            'customers' => Customer::with('user:id,name,email')->latest()->limit(500)->get()
                ->mapWithKeys(fn ($c) => [$c->id => $c->user->name.' — '.$c->user->email]),
            'services' => Service::active()->orderBy('name')->pluck('name', 'id'),
            'selectedCustomer' => $request->integer('customer') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('status', true)],
            'business_id' => ['nullable', Rule::exists('businesses', 'id')->where('customer_id', $request->integer('customer_id'))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer = Customer::with('user')->findOrFail($data['customer_id']);
        $business = isset($data['business_id']) ? $customer->businesses()->find($data['business_id']) : $customer->businesses()->orderByDesc('is_primary')->first();

        $application = $this->applications->submit($customer, Service::findOrFail($data['service_id']), ['notes' => $data['notes'] ?? null], [], $business, $request->user());
        $this->applications->assign($application, $request->user()->id, null, $request->user());

        return redirect()->route('admin.applications.show', $application)->with('success', "Application {$application->application_no} created for {$customer->user->name}.");
    }

    public function show(Request $request, Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'customer.user', 'business', 'service.fields', 'service.documents', 'staff', 'professional',
            'documents.uploader:id,name', 'documents.reviewer:id,name', 'documentRequests.requester:id,name',
            'notes.user:id,name', 'histories.user:id,name', 'payments.recorder:id,name', 'invoice', 'tasks.assignee:id,name',
        ]);

        return view('admin.applications.show', [
            'application' => $application,
            'statuses' => ApplicationStatus::options(),
            'staff' => User::backoffice()->where('user_type', User::TYPE_STAFF)->where('status', 'active')->orderBy('name')->pluck('name', 'id'),
            'professionals' => Professional::active()->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->name.' ('.$p->typeLabel().')']),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function status(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $data = $request->validate([
            'status' => ['required', new Enum(ApplicationStatus::class)],
            'remarks' => ['nullable', 'string', 'max:500'],
            'notify' => ['nullable', 'boolean'],
        ]);

        $to = ApplicationStatus::from($data['status']);
        if ($to === ApplicationStatus::Completed && $application->payment_status === PaymentStatus::Pending && (float) $application->total > 0 && ! $request->boolean('force')) {
            return back()->with('warning', 'Payment is still pending. Tick "complete anyway" to mark this application completed.');
        }

        $this->applications->changeStatus($application, $to, $request->user(), $data['remarks'] ?? null, $request->boolean('notify', true));

        return back()->with('success', 'Status updated to '.$to->label().'.');
    }

    public function assign(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('assign', $application);

        $data = $request->validate([
            'assigned_staff_id' => ['nullable', Rule::exists('users', 'id')->where('user_type', User::TYPE_STAFF)],
            'assigned_professional_id' => ['nullable', 'exists:professionals,id'],
        ]);

        $this->applications->assign($application, $data['assigned_staff_id'] ?? null, $data['assigned_professional_id'] ?? null, $request->user());

        return back()->with('success', 'Assignment updated.');
    }

    public function note(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:3000'],
            'visible_to_customer' => ['nullable', 'boolean'],
        ]);

        $this->applications->addNote($application, $data['note'], ! $request->boolean('visible_to_customer'), $request->user());

        return redirect()->to(route('admin.applications.show', $application).'#tab-notes')->with('success', 'Note added.');
    }

    public function requestDocument(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $data = $request->validate([
            'document_name' => ['required', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->applications->requestDocument($application, $data['document_name'], $data['note'] ?? null, $request->user());

        return redirect()->to(route('admin.applications.show', $application).'#tab-documents')->with('success', 'Document requested — the customer has been notified.');
    }

    /** Upload the final certificate / deliverable for the customer. */
    public function deliverable(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);
        abort_unless($request->user()->can('documents.upload_final'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'file' => DocumentService::fileRules(),
        ]);

        $this->documents->store($application, $request->file('file'), $data['name'], $request->user(), ApplicationDocument::TYPE_DELIVERABLE);
        activity('documents')->performedOn($application)->causedBy($request->user())->log("Uploaded final document \"{$data['name']}\"");

        return redirect()->to(route('admin.applications.show', $application).'#tab-documents')->with('success', 'Final document uploaded and shared with the customer.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->authorize('delete', $application);

        if ($application->payments()->where('status', PaymentStatus::Paid)->exists()) {
            return back()->with('error', 'Applications with payments cannot be deleted. Cancel it instead.');
        }

        $application->delete();

        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
    }
}
