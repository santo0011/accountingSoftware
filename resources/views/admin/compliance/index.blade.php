@extends('layouts.admin')
@section('title', (string) ('Compliance'))

@section('content')
<x-page-header title="Compliance" subtitle="Recurring statutory due dates for all customers. Statuses and reminders update automatically every day.">
    @can('compliance.manage')
        <a href="{{ route('admin.compliance-types.index') }}" class="btn btn-light"><i class="bi bi-sliders me-1"></i>Compliance Types</a>
        <a href="{{ route('admin.compliance.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Record</a>
    @endcan
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3"><x-stat-card label="Overdue" :value="$counts['overdue'] ?? 0" icon="bi-exclamation-octagon" color="red" :href="route('admin.compliance.index', ['status' => 'overdue'])" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Due soon" :value="$counts['due_soon'] ?? 0" icon="bi-hourglass-split" color="amber" :href="route('admin.compliance.index', ['status' => 'due_soon'])" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Upcoming" :value="$counts['upcoming'] ?? 0" icon="bi-calendar-event" :href="route('admin.compliance.index', ['status' => 'upcoming'])" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Completed" :value="$counts['completed'] ?? 0" icon="bi-check2-circle" color="green" :href="route('admin.compliance.index', ['status' => 'completed'])" /></div>
</div>

<form class="filter-bar row g-2" method="GET">
    <div class="col-6 col-md-3"><select name="status" class="form-select"><option value="">All pending</option>@foreach (\App\Enums\ComplianceStatus::options() as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><select name="type" class="form-select"><option value="">Any type</option>@foreach ($types as $id => $n)<option value="{{ $id }}" @selected(request('type') == $id)>{{ $n }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Due from"></div>
    <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Due to"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th class="col-sl">#</th><th>Due date</th><th>Compliance</th><th>Customer</th><th>Assigned &amp; reminder</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($records as $r)
                <tr>
                    <td class="col-sl" data-label="#">{{ $records->firstItem() + $loop->index }}</td>
                    <td data-label="Due date" class="text-nowrap stack-multi">
                        <span class="fw-semibold d-block {{ $r->status === \App\Enums\ComplianceStatus::Overdue ? 'text-danger' : 'text-navy' }}">{{ $r->due_date->format('d M Y') }}</span>
                        <small class="text-muted d-block">{{ $r->completed_at ? 'Done '.$r->completed_at->format('d M') : ($r->daysLeft() < 0 ? abs($r->daysLeft()).'d overdue' : 'in '.$r->daysLeft().'d') }}</small>
                    </td>
                    <td data-label="Compliance" class="stack-multi td-clip" title="{{ $r->title }}">
                        <span class="d-block">{{ $r->title }}</span>
                        <small class="text-muted d-block">{{ $r->period_label }} · {{ \App\Models\ComplianceType::FREQUENCIES[$r->frequency] ?? $r->frequency }}</small>
                    </td>
                    <td data-label="Customer" class="stack-multi td-clip" title="{{ $r->customer->user->name }}{{ $r->business ? ' · '.$r->business->name : '' }}">
                        <a href="{{ route('admin.customers.show', $r->customer_id) }}" class="d-block">{{ $r->customer->user->name }}</a>
                        @if ($r->business)<small class="text-muted d-block">{{ $r->business->name }}</small>@endif
                    </td>
                    <td data-label="Assigned &amp; reminder" class="text-nowrap stack-multi">
                        <span class="d-block">{{ $r->staff?->name ?? 'Unassigned' }}</span>
                        <small class="text-muted d-block"><i class="bi bi-bell me-1"></i>{{ $r->reminded_at ? 'Sent '.$r->reminded_at->format('d M') : ($r->reminder_date?->format('d M Y') ?? 'No reminder') }}</small>
                    </td>
                    <td data-label="Status" class="text-nowrap"><x-status-badge :status="$r->status" /></td>
                    <td class="td-actions text-end text-nowrap">
                        @can('compliance.manage')
                            @if ($r->status !== \App\Enums\ComplianceStatus::Completed)
                                <form method="POST" action="{{ route('admin.compliance.complete', $r) }}" class="d-inline" data-confirm="Mark {{ $r->title }} ({{ $r->period_label }}) as filed/completed?">@csrf<button class="btn btn-sm btn-cta" title="Mark completed"><i class="bi bi-check-lg"></i> Done</button></form>
                            @endif
                            <a href="{{ route('admin.compliance.edit', $r) }}" class="btn btn-sm btn-light" title="Edit" aria-label="Edit {{ $r->title }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.compliance.destroy', $r) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Delete" aria-label="Delete {{ $r->title }}"><i class="bi bi-trash"></i></button></form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="bi-calendar-check" title="No compliance records" text="Records are created automatically when a recurring service is completed, or add one manually." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$records" label="records" />
</div>
@endsection
