@extends('layouts.portal')
@section('title', (string) ('Ticket '.$ticket->ticket_no))

@section('content')
<x-page-header :title="$ticket->subject" :subtitle="$ticket->ticket_no.' · '.$ticket->categoryLabel()" :back="route('portal.support.index')">
    <x-status-badge :status="$ticket->status" class="fs-6" />
</x-page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4"><div class="card-body">@include('shared.ticket-thread', ['area' => 'portal'])</div></div>

        @can('reply', $ticket)
            <form method="POST" action="{{ route('portal.support.reply', $ticket) }}" enctype="multipart/form-data" class="card" novalidate>
                @csrf
                <div class="card-body">
                    <x-form.textarea name="message" label="Your reply" rows="4" required />
                    <x-form.input name="attachment" type="file" label="Attachment (optional)" accept="{{ \App\Support\FileTypes::accept(\App\Support\FileTypes::DOCUMENTS) }}" col="mb-0" />
                </div>
                <div class="card-footer bg-white text-end"><button class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Reply</button></div>
            </form>
        @else
            <div class="alert alert-info">This ticket is closed. <a href="{{ route('portal.support.create') }}">Open a new ticket</a> if you need more help.</div>
        @endcan
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <dl class="dl-grid">
                <dt>Ticket</dt><dd>{{ $ticket->ticket_no }}</dd>
                <dt>Priority</dt><dd><span class="badge badge-soft-{{ $ticket->priorityColor() }}">{{ ucfirst($ticket->priority) }}</span></dd>
                <dt>Created</dt><dd>{{ $ticket->created_at->format('d M Y') }}</dd>
                @if ($ticket->application)<dt>Application</dt><dd><a href="{{ route('portal.applications.show', $ticket->application) }}">{{ $ticket->application->application_no }}</a></dd>@endif
            </dl>
        </div></div>
    </div>
</div>
@endsection
