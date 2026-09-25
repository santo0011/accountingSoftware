@extends('layouts.admin')
@section('title', (string) ('Ticket '.$ticket->ticket_no))

@section('content')
<x-page-header :title="$ticket->subject" :subtitle="$ticket->ticket_no.' · '.$ticket->categoryLabel().' · opened '.$ticket->created_at->format('d M Y')" :back="route('admin.support.index')">
    <x-status-badge :status="$ticket->status" class="fs-6" />
</x-page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4"><div class="card-body">@include('shared.ticket-thread', ['area' => 'admin'])</div></div>

        @can('reply', $ticket)
            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" enctype="multipart/form-data" class="card" novalidate>
                @csrf
                <div class="card-body">
                    <x-form.textarea name="message" label="Reply" rows="4" required />
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5"><input type="file" name="attachment" class="form-control form-control-sm"></div>
                        <div class="col-md-4">
                            <select name="status" class="form-select form-select-sm"><option value="">Status: auto (waiting for customer)</option>@foreach (\App\Enums\TicketStatus::options() as $v => $l)<option value="{{ $v }}">Set: {{ $l }}</option>@endforeach</select>
                        </div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="internal" value="1" id="internal"><label class="form-check-label small" for="internal">Internal note</label></div></div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="bi bi-send me-1"></i>Send</button></div>
            </form>
        @endcan
    </div>
    <div class="col-lg-4">
        <form method="POST" action="{{ route('admin.support.update', $ticket) }}" class="card mb-4">
            @csrf @method('PUT')
            <div class="card-header">Ticket settings</div>
            <div class="card-body">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm mb-2">@foreach (\App\Enums\TicketStatus::options() as $v => $l)<option value="{{ $v }}" @selected($ticket->status->value === $v)>{{ $l }}</option>@endforeach</select>
                <label class="form-label small">Priority</label>
                <select name="priority" class="form-select form-select-sm mb-2">@foreach (\App\Models\SupportTicket::PRIORITIES as $v => $l)<option value="{{ $v }}" @selected($ticket->priority === $v)>{{ $l }}</option>@endforeach</select>
                <label class="form-label small">Assigned to</label>
                <select name="assigned_to" class="form-select form-select-sm mb-3"><option value="">Unassigned</option>@foreach ($staff as $id => $n)<option value="{{ $id }}" @selected($ticket->assigned_to === $id)>{{ $n }}</option>@endforeach</select>
                <button class="btn btn-sm btn-outline-primary w-100">Update</button>
            </div>
        </form>
        <div class="card"><div class="card-body">
            <dl class="dl-grid">
                <dt>Customer</dt><dd><a href="{{ route('admin.customers.show', $ticket->customer) }}">{{ $ticket->customer->user->name }}</a></dd>
                <dt>Email</dt><dd>{{ $ticket->customer->user->email }}</dd>
                <dt>Mobile</dt><dd>{{ $ticket->customer->user->mobile ?? '—' }}</dd>
                @if ($ticket->application)<dt>Application</dt><dd><a href="{{ route('admin.applications.show', $ticket->application) }}">{{ $ticket->application->application_no }}</a><br><small class="text-muted">{{ $ticket->application->service->name }}</small></dd>@endif
            </dl>
        </div></div>
    </div>
</div>
@endsection
