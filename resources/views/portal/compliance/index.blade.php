@extends('layouts.portal')
@section('title', (string) ('Compliance Calendar'))

@section('content')
<x-page-header title="Compliance Calendar" subtitle="Your upcoming statutory due dates. We send reminders before each one." />

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><x-stat-card label="Overdue" :value="$counts['overdue']" icon="bi-exclamation-octagon" color="red" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Due soon" :value="$counts['due_soon']" icon="bi-hourglass-split" color="amber" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Upcoming" :value="$counts['upcoming']" icon="bi-calendar-event" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Completed" :value="$counts['completed']" icon="bi-check2-circle" color="green" /></div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        @forelse ($upcoming as $month => $records)
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-calendar3 me-2 text-brand"></i>{{ $month }}</div>
                @foreach ($records as $record)
                    <div class="list-row">
                        <div class="text-center" style="width:48px">
                            <div class="small text-muted text-uppercase lh-1">{{ $record->due_date->format('D') }}</div>
                            <div class="fs-4 fw-bold text-navy lh-1">{{ $record->due_date->format('d') }}</div>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="title">{{ $record->title }} @if ($record->period_label)<span class="text-muted fw-normal">· {{ $record->period_label }}</span>@endif</div>
                            <div class="meta">
                                {{ $record->business?->name ?? 'All businesses' }}
                                · @php($d = $record->daysLeft()){{ $d < 0 ? abs($d).' days overdue' : ($d === 0 ? 'Due today' : 'Due in '.$d.' days') }}
                            </div>
                        </div>
                        <x-status-badge :status="$record->status" />
                    </div>
                @endforeach
            </div>
        @empty
            <div class="card"><x-empty-state icon="bi-calendar-check" title="No upcoming compliance" text="Subscribe to a recurring service (GST returns, TDS, annual compliance) and its due dates appear here automatically.">
                <a href="{{ route('site.categories.show', 'annual-compliance') }}" class="btn btn-sm btn-cta">Explore Compliance Services</a>
            </x-empty-state></div>
        @endforelse
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">Recently completed</div>
            @forelse ($completed as $record)
                <div class="list-row">
                    <i class="bi bi-check-circle-fill text-green"></i>
                    <div class="min-w-0 flex-grow-1"><div class="title">{{ $record->title }}</div><div class="meta">{{ $record->period_label }} · {{ $record->completed_at?->format('d M Y') }}</div></div>
                </div>
            @empty
                <p class="small text-muted p-3 mb-0">Nothing completed yet.</p>
            @endforelse
        </div>
        <div class="card">
            <div class="card-body">
                <div class="fw-semibold text-navy mb-2"><i class="bi bi-bell me-1"></i> How reminders work</div>
                <p class="small text-muted mb-0">We notify you by email and in this portal before each due date, and your assigned expert prepares the filing. Overdue filings may attract late fees and interest.</p>
            </div>
        </div>
    </div>
</div>
@endsection
