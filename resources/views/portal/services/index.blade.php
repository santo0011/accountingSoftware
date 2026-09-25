@extends('layouts.portal')
@section('title', (string) ('My Services'))

@section('content')
<x-page-header title="My Services" subtitle="Services you have engaged us for, including recurring compliance services.">
    <a href="{{ route('site.services.index') }}" class="btn btn-cta"><i class="bi bi-plus-lg me-1"></i>Add a Service</a>
</x-page-header>

@if ($inProgress->isNotEmpty())
    <h2 class="h6 small-caps text-muted mb-3">In progress</h2>
    <div class="row g-3 mb-4">
        @foreach ($inProgress as $app)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('portal.applications.show', $app) }}" class="stat-card align-items-start">
                    <span class="icon-bubble"><i class="bi {{ $app->service->iconClass() }}"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-semibold text-navy">{{ $app->service->name }}</div>
                        <div class="small text-muted mb-2">{{ $app->application_no }}</div>
                        <x-status-badge :status="$app->status" />
                        <div class="progress mt-2" style="height:5px"><div class="progress-bar bg-success" style="width: {{ round($app->status->stage() / 7 * 100) }}%"></div></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif

<h2 class="h6 small-caps text-muted mb-3">Active & completed services</h2>
@forelse ($subscriptions as $sub)
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center">
            <span class="icon-bubble"><i class="bi {{ $sub->service->iconClass() }}"></i></span>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-navy">{{ $sub->service->name }}</div>
                <div class="small text-muted">
                    {{ $sub->business?->name ?? 'Personal' }} · Since {{ $sub->start_date?->format('d M Y') }}
                    @if ($sub->isRecurring()) · {{ \App\Models\Service::INTERVALS[$sub->recurring_interval] ?? '' }} @endif
                </div>
            </div>
            @if ($sub->isRecurring() && ($next = $sub->complianceRecords->first()))
                <div class="text-end small">
                    <div class="text-muted">Next due</div>
                    <div class="fw-semibold text-navy">{{ $next->due_date->format('d M Y') }}</div>
                </div>
            @endif
            <span class="badge badge-soft-{{ $sub->statusColor() }}">{{ \App\Models\CustomerService::STATUSES[$sub->status] ?? $sub->status }}</span>
        </div>
    </div>
@empty
    <div class="card"><x-empty-state icon="bi-briefcase" title="No services yet" text="Once an application is completed it appears here. Recurring services also show their next due date.">
        <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-sm">Browse Services</a>
    </x-empty-state></div>
@endforelse
@endsection
