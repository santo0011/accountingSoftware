@extends('layouts.portal')
@section('title', (string) ('Dashboard'))

@section('content')
@php
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, " ");
    $business = $customer->businesses()->orderByDesc("is_primary")->value("name");
    $attention = $stats["pending_payments"] + $stats["pending_documents"];
    // [label, value, icon, tone, link, hint, trend]
    $kpis = [
        ["Active applications", $stats["active"], "bi-folder2-open", "blue", route("portal.applications.index", ["filter" => "active"]), $stats["active"] ? "In progress" : "None right now", "neutral"],
        ["Completed services", $stats["completed"], "bi-patch-check", "green", route("portal.applications.index", ["filter" => "completed"]), "All time", "up"],
        ["Pending documents", $stats["pending_documents"], "bi-file-earmark-arrow-up", "amber", route("portal.documents.index"), $stats["pending_documents"] ? "Upload needed" : "All clear", $stats["pending_documents"] ? "warn" : "up"],
        ["Pending payments", $stats["pending_payments"], "bi-credit-card", "rose", route("portal.payments.index"), $stats["pending_payments"] ? "Payment due" : "All paid", $stats["pending_payments"] ? "down" : "up"],
    ];
@endphp

{{-- Welcome banner --}}
<div class="dash-hero">
    <div class="dash-hero-text">
        <div class="dash-hero-date"><i class="bi bi-sun"></i> {{ now()->format("l, d F Y") }}</div>
        <h1>Welcome back, {{ $firstName }} 👋</h1>
        <p>@if ($business){{ $business }} · @endif Here is the status of your business services.</p>
        @if ($attention)
            <a href="{{ $stats["pending_payments"] ? route("portal.payments.index") : route("portal.documents.index") }}" class="dash-hero-alert">
                <span class="pulse-dot"></span>{{ $attention }} item(s) need your attention <i class="bi bi-arrow-right"></i>
            </a>
        @elseif ($stats["upcoming_compliance"])
            <a href="{{ route("portal.compliance.index") }}" class="dash-hero-alert">
                <i class="bi bi-calendar-event"></i>{{ $stats["upcoming_compliance"] }} compliance due date(s) in the next 30 days <i class="bi bi-arrow-right"></i>
            </a>
        @endif
    </div>
    <div class="dash-hero-actions">
        <a href="{{ route("site.services.index") }}" class="btn btn-light"><i class="bi bi-plus-lg me-1"></i>Apply for a Service</a>
        <a href="{{ route("portal.support.create") }}" class="btn btn-ghost-light"><i class="bi bi-headset me-1"></i>Get Help</a>
    </div>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    @foreach ($kpis as [$label, $value, $icon, $tone, $href, $hint, $trend])
        <div class="col-6 col-xl-3">
            <a href="{{ $href }}" class="kpi kpi-{{ $tone }}">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="bi {{ $icon }}"></i></span>
                    <i class="bi bi-arrow-up-right kpi-go"></i>
                </div>
                <div class="kpi-value">{{ $value }}</div>
                <div class="kpi-label">{{ $label }}</div>
                <span class="kpi-hint kpi-hint-{{ $trend }}">@if ($trend === "up")<i class="bi bi-check2"></i>@elseif ($trend === "down")<i class="bi bi-exclamation-circle"></i>@elseif ($trend === "warn")<i class="bi bi-clock"></i>@endif{{ $hint }}</span>
            </a>
        </div>
    @endforeach
</div>

{{-- Quick actions --}}
<div class="row g-3 mb-4">
    @foreach ([
        [route("site.services.index"), "bi-plus-circle", "Apply for a Service", "Browse 50+ services", "blue"],
        [route("portal.documents.index"), "bi-cloud-upload", "Upload Document", "Share files securely", "amber"],
        [route("portal.payments.index"), "bi-wallet2", "Make Payment", "UPI or bank transfer", "green"],
        [route("portal.support.create"), "bi-headset", "Contact Support", "We reply within a day", "violet"],
    ] as [$url, $icon, $label, $sub, $tone])
        <div class="col-6 col-lg-3">
            <a href="{{ $url }}" class="qa kpi-{{ $tone }}">
                <span class="kpi-icon"><i class="bi {{ $icon }}"></i></span>
                <span class="qa-text"><strong>{{ $label }}</strong><small>{{ $sub }}</small></span>
                <i class="bi bi-chevron-right qa-go"></i>
            </a>
        </div>
    @endforeach
</div>

@if ($actionNeeded->isNotEmpty())
    <div class="card mb-4 border-warning-subtle">
        <div class="card-header d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle text-warning"></i> Action needed</div>
        @foreach ($actionNeeded as $app)
            <div class="list-row">
                <div class="min-w-0 flex-grow-1">
                    <div class="title">{{ $app->service->name }} <span class="text-muted fw-normal">· {{ $app->application_no }}</span></div>
                    <div class="meta">
                        @if ($app->status === \App\Enums\ApplicationStatus::DocumentsPending) Documents are required to continue. @endif
                        @if (! $app->isPaid()) Payment of {{ money($app->total) }} is pending. @endif
                    </div>
                </div>
                @if (! $app->isPaid())
                    <a href="{{ route('portal.payments.checkout', $app) }}" class="btn btn-sm btn-cta">Pay Now</a>
                @endif
                <a href="{{ route('portal.applications.show', $app) }}" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        @endforeach
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                Active applications
                <a href="{{ route('portal.applications.index') }}" class="small fw-semibold">View all</a>
            </div>
            @forelse ($activeApplications as $app)
                <a href="{{ route('portal.applications.show', $app) }}" class="list-row">
                    <span class="icon-bubble sm"><i class="bi {{ $app->service->iconClass() }}"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="title text-truncate">{{ $app->service->name }}</div>
                        <div class="meta">{{ $app->application_no }} · {{ $app->created_at->format('d M Y') }}</div>
                        <div class="progress mt-2" style="height:5px"><div class="progress-bar bg-success" style="width: {{ round($app->status->stage() / 7 * 100) }}%"></div></div>
                    </div>
                    <x-status-badge :status="$app->status" />
                </a>
            @empty
                <x-empty-state icon="bi-folder2-open" title="No active applications" text="Apply for a service to get started.">
                    <a href="{{ route('site.services.index') }}" class="btn btn-sm btn-cta">Browse Services</a>
                </x-empty-state>
            @endforelse
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Upcoming compliance
                <a href="{{ route('portal.compliance.index') }}" class="small fw-semibold">Calendar</a>
            </div>
            @forelse ($compliance as $record)
                <div class="list-row">
                    <div class="text-center" style="width:44px">
                        <div class="small text-muted text-uppercase lh-1">{{ $record->due_date->format('M') }}</div>
                        <div class="fs-5 fw-bold text-navy lh-1">{{ $record->due_date->format('d') }}</div>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="title text-truncate">{{ $record->title }}</div>
                        <div class="meta">{{ $record->period_label }}@if ($record->business) · {{ $record->business->name }}@endif</div>
                    </div>
                    <x-status-badge :status="$record->status" />
                </div>
            @empty
                <x-empty-state icon="bi-calendar-check" title="No upcoming due dates" text="Recurring services add due dates here automatically." />
            @endforelse
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Recent payments
                <a href="{{ route('portal.payments.index') }}" class="small fw-semibold">View all</a>
            </div>
            @forelse ($payments as $payment)
                <div class="list-row">
                    <span class="icon-bubble sm green"><i class="bi bi-currency-rupee"></i></span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="title">{{ money($payment->amount) }}</div>
                        <div class="meta text-truncate">{{ $payment->application?->service?->name }} · {{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</div>
                    </div>
                    <x-status-badge :status="$payment->status" />
                </div>
            @empty
                <x-empty-state icon="bi-credit-card" title="No payments yet" />
            @endforelse
        </div>
    </div>
</div>

@if ($suggested->isNotEmpty())
    <h2 class="h5 mt-5 mb-3">Recommended for your business</h2>
    <div class="row g-3">
        @foreach ($suggested as $service)
            <div class="col-md-4"><x-site.service-card :service="$service" /></div>
        @endforeach
    </div>
    @push('head')<link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=1">@endpush
@endif
@endsection
