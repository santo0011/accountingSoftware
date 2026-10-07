@extends('layouts.portal')
@section('title', (string) ('Support'))

@section('content')
<x-page-header title="Support" subtitle="Raise a ticket and our team will get back to you.">
    <a href="{{ route('portal.support.create') }}" class="btn btn-cta"><i class="bi bi-plus-lg me-1"></i>New Ticket</a>
</x-page-header>

<div class="table-card">
    @if ($tickets->isEmpty())
        <x-empty-state icon="bi-headset" title="No support tickets" text="Have a question? We're happy to help.">
            <a href="{{ route('portal.support.create') }}" class="btn btn-sm btn-cta">Create a Ticket</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Ticket</th><th>Category</th><th>Priority</th><th>Last update</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $tickets->firstItem() + $loop->index }}</td>
                        <td data-label="Ticket" class="stack-multi td-clip wide" title="{{ $ticket->subject }}">
                            <a href="{{ route('portal.support.show', $ticket) }}" class="fw-semibold d-block">{{ $ticket->subject }}</a>
                            <small class="text-muted d-block">{{ $ticket->ticket_no }} · {{ $ticket->messages_count }} {{ \Illuminate\Support\Str::plural('message', $ticket->messages_count) }}</small>
                        </td>
                        <td data-label="Category" class="text-nowrap">{{ $ticket->categoryLabel() }}</td>
                        <td data-label="Priority"><span class="badge badge-soft-{{ $ticket->priorityColor() }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td data-label="Last update" class="text-nowrap" title="{{ $ticket->last_reply_at?->format('d M Y, h:i A') }}">{{ $ticket->last_reply_at?->diffForHumans(short: true) ?? '—' }}</td>
                        <td data-label="Status" class="text-nowrap"><x-status-badge :status="$ticket->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$tickets" label="tickets" />
    @endif
</div>
@endsection
