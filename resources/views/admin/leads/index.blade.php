@extends('layouts.admin')
@section('title', (string) ('Leads'))

@section('content')
<x-page-header title="Leads" subtitle="Website enquiries and prospects — follow up and convert them into customers.">
    @can('leads.create')<a href="{{ route('admin.leads.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Lead</a>@endcan
</x-page-header>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.leads.index') }}" class="btn btn-sm {{ ! request('status') ? 'btn-primary' : 'btn-light' }}">All <span class="opacity-75">{{ $counts->sum() }}</span></a>
    @foreach ($statuses as $v => $l)
        <a href="{{ route('admin.leads.index', ['status' => $v]) }}" class="btn btn-sm {{ request('status') === $v ? 'btn-primary' : 'btn-light' }}">{{ $l }} <span class="opacity-75">{{ $counts[$v] ?? 0 }}</span></a>
    @endforeach
    <a href="{{ route('admin.leads.index', ['followup_due' => 1]) }}" class="btn btn-sm {{ request('followup_due') ? 'btn-warning' : 'btn-light' }}"><i class="bi bi-alarm me-1"></i>Follow-ups due</a>
</div>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, email, phone, company"></div>
    <div class="col-6 col-md-2"><select name="source" class="form-select"><option value="">Any source</option>@foreach ($sources as $v => $l)<option value="{{ $v }}" @selected(request('source') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><select name="assigned" class="form-select"><option value="">Anyone</option><option value="none" @selected(request('assigned') === 'none')>Unassigned</option>@foreach ($staff as $id => $n)<option value="{{ $id }}" @selected(request('assigned') == $id)>{{ $n }}</option>@endforeach</select></div>
    <input type="hidden" name="status" value="{{ request('status') }}">
    <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1" data-no-lock>Filter</button><a href="{{ route('admin.leads.index') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a></div>
</form>

<div class="table-card">
    @if ($leads->isEmpty())
        <x-empty-state icon="bi-person-lines-fill" title="No leads found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Lead</th><th>Contact</th><th>Interested in</th><th>Assigned</th><th>Next follow-up</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($leads as $lead)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $leads->firstItem() + $loop->index }}</td>
                        <td data-label="Lead" class="stack-multi td-clip" title="{{ $lead->name }}{{ $lead->company ? ' · '.$lead->company : '' }}">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="fw-semibold d-block">{{ $lead->name }}</a>
                            <small class="text-muted d-block">{{ $lead->company ?: 'Individual' }}</small>
                        </td>
                        <td data-label="Contact" class="stack-multi td-clip" title="{{ $lead->email }}">
                            <span class="d-block">{{ $lead->phone }}</span>
                            <small class="text-muted d-block">{{ $lead->email ?: '—' }}</small>
                        </td>
                        <td data-label="Interested in" class="stack-multi td-clip" title="{{ $lead->service?->name }}">
                            <span class="d-block">{{ $lead->service?->name ?? 'Not specified' }}</span>
                            <small class="text-muted d-block">via {{ $lead->sourceLabel() }}</small>
                        </td>
                        <td data-label="Assigned" class="text-nowrap">{{ $lead->assignee?->name ?? '—' }}</td>
                        <td data-label="Next follow-up" class="text-nowrap {{ $lead->next_followup_at?->isPast() ? 'text-danger fw-semibold' : '' }}">{{ $lead->next_followup_at?->format('d M Y') ?? '—' }}</td>
                        <td data-label="Status" class="text-nowrap"><x-status-badge :status="$lead->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$leads" label="leads" />
    @endif
</div>
@endsection
