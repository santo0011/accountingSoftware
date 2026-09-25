@extends('layouts.admin')
@section('title', (string) ('Support'))

@section('content')
<x-page-header title="Support tickets" subtitle="Customer questions and issues." />

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.support.index') }}" class="btn btn-sm {{ ! request('status') ? 'btn-primary' : 'btn-light' }}">Open</a>
    @foreach (\App\Enums\TicketStatus::options() as $v => $l)
        <a href="{{ route('admin.support.index', ['status' => $v]) }}" class="btn btn-sm {{ request('status') === $v ? 'btn-primary' : 'btn-light' }}">{{ $l }} <span class="opacity-75">{{ $counts[$v] ?? 0 }}</span></a>
    @endforeach
</div>

<form class="filter-bar row g-2" method="GET">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ticket no. or subject"></div>
    <div class="col-6 col-md-3"><select name="category" class="form-select"><option value="">Any category</option>@foreach (\App\Models\SupportTicket::CATEGORIES as $v => $l)<option value="{{ $v }}" @selected(request('category') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><select name="priority" class="form-select"><option value="">Any priority</option>@foreach (\App\Models\SupportTicket::PRIORITIES as $v => $l)<option value="{{ $v }}" @selected(request('priority') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th>Ticket</th><th>Customer</th><th>Category</th><th>Priority</th><th>Assigned</th><th>Last activity</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($tickets as $t)
                <tr>
                    <td data-label="Ticket"><a href="{{ route('admin.support.show', $t) }}" class="fw-semibold">{{ $t->subject }}</a><br><small class="text-muted">{{ $t->ticket_no }} · {{ $t->messages_count }} messages</small></td>
                    <td data-label="Customer">{{ $t->customer->user->name }}</td>
                    <td data-label="Category">{{ $t->categoryLabel() }}</td>
                    <td data-label="Priority"><span class="badge badge-soft-{{ $t->priorityColor() }}">{{ ucfirst($t->priority) }}</span></td>
                    <td data-label="Assigned">{{ $t->assignee?->name ?? '—' }}</td>
                    <td data-label="Last activity">{{ $t->last_reply_at?->diffForHumans() }}</td>
                    <td data-label="Status"><x-status-badge :status="$t->status" /></td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="bi-headset" title="No tickets" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $tickets->total() }} tickets</span>{{ $tickets->links() }}</div>
</div>
@endsection
