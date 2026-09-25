@extends('layouts.portal')
@section('title', (string) ('Dashboard'))

@section('content')
<div class="welcome-card mb-4">
    <div class="row align-items-center g-3 position-relative" style="z-index:1">
        <div class="col-lg-7">
            <h2 class="mb-1">Welcome, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }} 👋</h2>
            <p class="mb-0">Here's what's happening with your business services today.</p>
        </div>
        <div class="col-lg-5 text-lg-end">
            <a href="{{ route('site.services.index') }}" class="btn btn-cta"><i class="bi bi-plus-lg me-1"></i>Apply for a Service</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl"><x-stat-card label="Active applications" :value="$stats['active']" icon="bi-folder2-open" :href="route('portal.applications.index', ['filter' => 'active'])" /></div>
    <div class="col-6 col-xl"><x-stat-card label="Completed services" :value="$stats['completed']" icon="bi-patch-check" color="green" :href="route('portal.applications.index', ['filter' => 'completed'])" /></div>
    <div class="col-6 col-xl"><x-stat-card label="Pending documents" :value="$stats['pending_documents']" icon="bi-file-earmark-arrow-up" color="amber" :href="route('portal.documents.index')" /></div>
    <div class="col-6 col-xl"><x-stat-card label="Pending payments" :value="$stats['pending_payments']" icon="bi-credit-card" color="red" :href="route('portal.payments.index')" /></div>
    <div class="col-12 col-xl"><x-stat-card label="Upcoming compliance (30 days)" :value="$stats['upcoming_compliance']" icon="bi-calendar-event" color="teal" :href="route('portal.compliance.index')" /></div>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        [route('site.services.index'), 'bi-plus-circle', 'Apply for a Service', ''],
        [route('portal.documents.index'), 'bi-cloud-upload', 'Upload Document', 'amber'],
        [route('portal.payments.index'), 'bi-wallet2', 'Make Payment', 'green'],
        [route('portal.support.create'), 'bi-headset', 'Contact Support', 'teal'],
    ] as [$url, $icon, $label, $color])
        <div class="col-6 col-md-3">
            <a href="{{ $url }}" class="quick-action"><span class="icon-bubble {{ $color }}"><i class="bi {{ $icon }}"></i></span>{{ $label }}</a>
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
