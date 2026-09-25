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
                <thead><tr><th>Ticket</th><th>Subject</th><th>Category</th><th>Priority</th><th>Last update</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td data-label="Ticket"><a href="{{ route('portal.support.show', $ticket) }}" class="fw-semibold">{{ $ticket->ticket_no }}</a></td>
                        <td data-label="Subject">{{ $ticket->subject }} <small class="text-muted">({{ $ticket->messages_count }})</small></td>
                        <td data-label="Category">{{ $ticket->categoryLabel() }}</td>
                        <td data-label="Priority"><span class="badge badge-soft-{{ $ticket->priorityColor() }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td data-label="Last update">{{ $ticket->last_reply_at?->diffForHumans() }}</td>
                        <td data-label="Status"><x-status-badge :status="$ticket->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $tickets->total() }} tickets</span>{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
