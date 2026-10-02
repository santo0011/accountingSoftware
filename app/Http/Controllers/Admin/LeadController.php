<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadRequest;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Service;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use RuntimeException;

class LeadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:leads.view', only: ['index', 'show']),
            new Middleware('permission:leads.create', only: ['create', 'store']),
            new Middleware('permission:leads.edit', only: ['edit', 'update', 'followup', 'convert']),
            new Middleware('permission:leads.delete', only: ['destroy']),
        ];
    }

    public function __construct(private LeadService $leads) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $leads = Lead::with('service:id,name', 'assignee:id,name')
            ->when(! $user->can('leads.view_all'), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('company', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->source))
            ->when($request->filled('assigned'), fn ($q) => $request->assigned === 'none' ? $q->whereNull('assigned_to') : $q->where('assigned_to', $request->assigned))
            ->when($request->boolean('followup_due'), fn ($q) => $q->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()->endOfDay()))
            ->latest()->paginate(per_page(20))->withQueryString();

        $counts = Lead::query()->when(! $user->can('leads.view_all'), fn ($q) => $q->where('assigned_to', $user->id))
            ->toBase()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.leads.index', ['leads' => $leads, 'counts' => $counts] + $this->options());
    }

    public function create(): View
    {
        return view('admin.leads.form', ['lead' => new Lead(['source' => 'phone'])] + $this->options());
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $lead = $this->leads->create($request->validated(), $request->user());

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead created.');
    }

    public function show(Request $request, Lead $lead): View
    {
        $this->authorizeLead($request, $lead);
        $lead->load('service', 'assignee', 'customer.user', 'followups.user:id,name', 'tasks.assignee:id,name');

        return view('admin.leads.show', ['lead' => $lead] + $this->options());
    }

    public function edit(Request $request, Lead $lead): View
    {
        $this->authorizeLead($request, $lead);

        return view('admin.leads.form', ['lead' => $lead] + $this->options());
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request, $lead);
        $this->leads->update($lead, $request->validated(), $request->user());

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }

    public function followup(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate([
            'channel' => ['required', Rule::in(array_keys(LeadFollowup::CHANNELS))],
            'remarks' => ['required', 'string', 'max:2000'],
            'status' => ['nullable', new Enum(LeadStatus::class)],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $this->leads->addFollowup($lead, $data, $request->user());

        return back()->with('success', 'Follow-up logged.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorizeLead($request, $lead);

        try {
            $customer = $this->leads->convert($lead, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Lead converted. The customer has been emailed a link to set their password.');
    }

    private function authorizeLead(Request $request, Lead $lead): void
    {
        abort_unless($request->user()->can('leads.view_all') || $lead->assigned_to === $request->user()->id, 403);
    }

    private function options(): array
    {
        return [
            'statuses' => LeadStatus::options(),
            'sources' => Lead::SOURCES,
            'services' => Service::orderBy('name')->pluck('name', 'id'),
            'staff' => User::backoffice()->where('status', 'active')->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
