<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplianceStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplianceRecord;
use App\Models\ComplianceType;
use App\Models\Customer;
use App\Models\User;
use App\Services\ComplianceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplianceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:compliance.view', only: ['index']),
            new Middleware('permission:compliance.manage', except: ['index']),
        ];
    }

    public function __construct(private ComplianceService $compliance) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $base = ComplianceRecord::query()->when(! $user->can('compliance.view_all'), fn ($q) => $q->where('assigned_staff_id', $user->id));

        $records = (clone $base)->with('customer.user:id,name', 'business:id,name', 'staff:id,name', 'type:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status), fn ($q) => $q->pending())
            ->when($request->filled('type'), fn ($q) => $q->where('compliance_type_id', $request->type))
            ->when($request->filled('customer'), fn ($q) => $q->where('customer_id', $request->customer))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('due_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('due_date', '<=', $request->to))
            ->orderBy('due_date')->paginate(25)->withQueryString();

        return view('admin.compliance.index', [
            'records' => $records,
            'counts' => (clone $base)->toBase()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'types' => ComplianceType::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.compliance.form', ['record' => new ComplianceRecord(['customer_id' => $request->integer('customer') ?: null])] + $this->options());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $type = ComplianceType::findOrFail($data['compliance_type_id']);
        $due = CarbonImmutable::parse($data['due_date']);

        ComplianceRecord::create($data + [
            'title' => $data['title'] ?: $type->name,
            'frequency' => $type->frequency,
            'reminder_date' => $due->subDays($type->reminder_days_before)->toDateString(),
            'status' => $this->compliance->statusFor($due, $type->reminder_days_before),
        ]);

        return redirect()->route('admin.compliance.index')->with('success', 'Compliance record added.');
    }

    public function edit(ComplianceRecord $record): View
    {
        return view('admin.compliance.form', ['record' => $record] + $this->options());
    }

    public function update(Request $request, ComplianceRecord $record): RedirectResponse
    {
        $data = $this->validated($request);
        $due = CarbonImmutable::parse($data['due_date']);
        $type = ComplianceType::findOrFail($data['compliance_type_id']);

        $record->update($data + [
            'title' => $data['title'] ?: $type->name,
            'reminder_date' => $due->subDays($type->reminder_days_before)->toDateString(),
            'reminded_at' => $record->due_date->isSameDay($due) ? $record->reminded_at : null,
            'status' => $record->status === ComplianceStatus::Completed ? ComplianceStatus::Completed : $this->compliance->statusFor($due, $type->reminder_days_before),
        ]);

        return redirect()->route('admin.compliance.index')->with('success', 'Compliance record updated.');
    }

    public function complete(Request $request, ComplianceRecord $record): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:500']]);
        $next = $this->compliance->complete($record, $request->user(), $data['remarks'] ?? null);

        return back()->with('success', 'Marked as completed.'.($next ? ' Next due date: '.$next->due_date->format('d M Y').'.' : ''));
    }

    public function destroy(ComplianceRecord $record): RedirectResponse
    {
        $record->delete();

        return back()->with('success', 'Compliance record deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'business_id' => ['nullable', Rule::exists('businesses', 'id')->where('customer_id', $request->integer('customer_id'))],
            'compliance_type_id' => ['required', 'exists:compliance_types,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'period_label' => ['nullable', 'string', 'max:50'],
            'due_date' => ['required', 'date'],
            'assigned_staff_id' => ['nullable', Rule::exists('users', 'id')->whereIn('user_type', ['staff', 'professional'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function options(): array
    {
        return [
            'customers' => Customer::with('user:id,name')->get()->mapWithKeys(fn ($c) => [$c->id => $c->user->name.' ('.$c->customer_code.')']),
            'types' => ComplianceType::where('status', true)->orderBy('name')->pluck('name', 'id'),
            'staff' => User::backoffice()->where('status', 'active')->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
