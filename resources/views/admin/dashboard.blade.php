@extends('layouts.admin')
@section('title', (string) ('Dashboard'))

@section('content')
@php
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, " ");
    $greeting = now()->hour < 12 ? "Good morning" : (now()->hour < 17 ? "Good afternoon" : "Good evening");
    // [label, value, icon, colour key, link, hint, hint tone, permission]
    $kpis = [
        ["Total customers", number_format($stats["customers"]), "bi-people", "blue", route("admin.customers.index"), "+".$stats["new_customers"]." this month", "up", "customers.view"],
        ["New leads", $stats["new_leads"], "bi-person-lines-fill", "violet", route("admin.leads.index", ["status" => "new"]), "Awaiting contact", "neutral", "leads.view"],
        ["Active applications", $stats["active_applications"], "bi-folder2-open", "sky", route("admin.applications.index", ["status" => "active"]), "In progress", "neutral", "applications.view"],
        ["Completed", $stats["completed_applications"], "bi-patch-check", "green", route("admin.applications.index", ["status" => "completed"]), "All time", "neutral", "applications.view"],
        ["Pending documents", $stats["pending_documents"], "bi-file-earmark-arrow-up", "amber", route("admin.documents.index"), $stats["pending_documents"] ? "Needs review" : "All clear", $stats["pending_documents"] ? "warn" : "up", "documents.view"],
        ["Pending payments", $stats["pending_payments"], "bi-hourglass-split", "rose", route("admin.applications.index", ["payment" => "pending"]), $stats["pending_payments"] ? "Follow up" : "All clear", $stats["pending_payments"] ? "down" : "up", "payments.view"],
        ["Revenue this month", money($stats["monthly_revenue"], false), "bi-currency-rupee", "green", route("admin.payments.index", ["status" => "paid"]), now()->format("F Y"), "neutral", "payments.view"],
        ["Compliance (30 days)", $stats["upcoming_compliance"], "bi-calendar-event", "teal", route("admin.compliance.index"), $overdueCompliance ? $overdueCompliance." overdue" : "On track", $overdueCompliance ? "down" : "up", "compliance.view"],
    ];
@endphp

{{-- Welcome banner --}}
<div class="dash-hero">
    <div class="dash-hero-text">
        <div class="dash-hero-date"><i class="bi bi-sun"></i> {{ now()->format("l, d F Y") }}</div>
        <h1>{{ $greeting }}, {{ $firstName }} 👋</h1>
        <p>Here is what is happening with your business today.</p>
        @if ($pendingVerification)
            <a href="{{ route("admin.payments.index", ["status" => "pending"]) }}" class="dash-hero-alert">
                <span class="pulse-dot"></span><strong>{{ $pendingVerification }}</strong> payment(s) waiting for verification <i class="bi bi-arrow-right"></i>
            </a>
        @endif
    </div>
    <div class="dash-hero-actions">
        @can("applications.create")<a href="{{ route("admin.applications.create") }}" class="btn btn-light"><i class="bi bi-plus-lg me-1"></i>New Application</a>@endcan
        @can("leads.create")<a href="{{ route("admin.leads.create") }}" class="btn btn-ghost-light"><i class="bi bi-person-plus me-1"></i>Add Lead</a>@endcan
    </div>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    @foreach ($kpis as [$label, $value, $icon, $tone, $href, $hint, $trend, $perm])
        @can($perm)
            <div class="col-6 col-xl-3">
                <a href="{{ $href }}" class="kpi kpi-{{ $tone }}">
                    <div class="kpi-top">
                        <span class="kpi-icon"><i class="bi {{ $icon }}"></i></span>
                        <i class="bi bi-arrow-up-right kpi-go"></i>
                    </div>
                    <div class="kpi-value">{{ $value }}</div>
                    <div class="kpi-label">{{ $label }}</div>
                    <span class="kpi-hint kpi-hint-{{ $trend }}">@if ($trend === "up")<i class="bi bi-arrow-up-short"></i>@elseif ($trend === "down")<i class="bi bi-exclamation-circle"></i>@elseif ($trend === "warn")<i class="bi bi-clock"></i>@endif{{ $hint }}</span>
                </a>
            </div>
        @endcan
    @endforeach
</div>

<div class="row g-4 mb-4">
    @if ($revenue)
        <div class="col-xl-8">
            <div class="card h-100 chart-card">
                <div class="card-header chart-card-head">
                    <div>
                        <div class="chart-card-title">Revenue</div>
                        <div class="chart-card-sub">Last 12 months</div>
                    </div>
                    <div class="text-end">
                        <div class="chart-card-total">{{ money(array_sum($revenue['values']), false) }}</div>
                        <div class="chart-card-sub">Total collected</div>
                    </div>
                </div>
                <div class="card-body"><div class="chart-box"><canvas id="revenueChart" aria-label="Revenue chart"></canvas></div></div>
            </div>
        </div>
    @endif
    <div class="{{ $revenue ? 'col-xl-4' : 'col-12' }}">
        <div class="card h-100 chart-card">
            <div class="card-header chart-card-head">
                <div>
                    <div class="chart-card-title">Applications</div>
                    <div class="chart-card-sub">By current status</div>
                </div>
            </div>
            <div class="card-body">
                @if ($byStatus)
                    <div class="donut-wrap">
                        <div class="chart-box" style="height:220px"><canvas id="statusChart" aria-label="Applications by status"></canvas></div>
                        <div class="donut-center"><strong>{{ array_sum($byStatus) }}</strong><span>Total</span></div>
                    </div>
                    <div class="donut-legend" id="statusLegend"></div>
                @else
                    <x-empty-state icon="bi-pie-chart" title="No applications yet" />
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">Recent applications <a href="{{ route('admin.applications.index') }}" class="small">View all</a></div>
            <div class="table-responsive">
                <table class="table table-hover table-stack">
                    <thead><tr><th>Application</th><th>Customer</th><th>Service</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse ($recentApplications as $app)
                        <tr>
                            <td data-label="Application"><a href="{{ route('admin.applications.show', $app) }}" class="fw-semibold">{{ $app->application_no }}</a><br><small class="text-muted">{{ $app->created_at->diffForHumans() }}</small></td>
                            <td data-label="Customer">{{ $app->customer->user->name }}</td>
                            <td data-label="Service">{{ $app->service->name }}</td>
                            <td data-label="Status"><x-status-badge :status="$app->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state title="No applications" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('payments.view')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">Recent payments <a href="{{ route('admin.payments.index') }}" class="small">View all</a></div>
                @forelse ($recentPayments as $payment)
                    <div class="list-row">
                        <span class="icon-bubble sm {{ $payment->status === \App\Enums\PaymentStatus::Paid ? 'green' : 'amber' }}"><i class="bi bi-currency-rupee"></i></span>
                        <div class="min-w-0 flex-grow-1"><div class="title">{{ $payment->customer->user->name }}</div><div class="meta">{{ $payment->payment_no }} · {{ $payment->methodLabel() }} · {{ $payment->created_at->format('d M') }}</div></div>
                        <strong class="text-navy">{{ money($payment->amount, false) }}</strong>
                        <x-status-badge :status="$payment->status" />
                    </div>
                @empty
                    <x-empty-state title="No payments yet" icon="bi-credit-card" />
                @endforelse
            </div>
        @endcan
    </div>

    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">My tasks <a href="{{ route('admin.tasks.index') }}" class="small">All tasks</a></div>
            @forelse ($myTasks as $task)
                <div class="list-row">
                    <form method="POST" action="{{ route('admin.tasks.complete', $task) }}">@csrf
                        <button class="btn btn-sm btn-light btn-icon" title="Mark complete" data-no-lock><i class="bi bi-check2"></i></button>
                    </form>
                    <div class="min-w-0 flex-grow-1"><div class="title text-truncate">{{ $task->title }}</div><div class="meta {{ $task->isOverdue() ? 'text-danger' : '' }}">{{ $task->due_date ? 'Due '.$task->due_date->format('d M') : 'No due date' }}</div></div>
                    <span class="badge badge-soft-{{ $task->priorityColor() }}">{{ ucfirst($task->priority) }}</span>
                </div>
            @empty
                <x-empty-state icon="bi-check2-all" title="No open tasks" text="You're all caught up." />
            @endforelse
        </div>

        @can('documents.view')
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">Documents to review <a href="{{ route('admin.documents.index') }}" class="small">Review queue</a></div>
                @forelse ($pendingDocuments as $doc)
                    <a href="{{ route('admin.applications.show', $doc->application) }}#tab-documents" class="list-row">
                        <i class="bi {{ $doc->icon() }} fs-5"></i>
                        <div class="min-w-0 flex-grow-1"><div class="title text-truncate">{{ $doc->name }}</div><div class="meta">{{ $doc->application->application_no }} · {{ $doc->application->customer->user->name }}</div></div>
                        <small class="text-muted">{{ $doc->created_at->diffForHumans(short: true) }}</small>
                    </a>
                @empty
                    <x-empty-state icon="bi-file-earmark-check" title="Nothing to review" />
                @endforelse
            </div>
        @endcan

        @can('compliance.view')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">Upcoming compliance <a href="{{ route('admin.compliance.index') }}" class="small">View all</a></div>
                @forelse ($upcomingCompliance as $record)
                    <div class="list-row">
                        <div class="text-center" style="width:42px"><div class="small text-muted lh-1">{{ $record->due_date->format('M') }}</div><div class="fw-bold text-navy fs-5 lh-1">{{ $record->due_date->format('d') }}</div></div>
                        <div class="min-w-0 flex-grow-1"><div class="title text-truncate">{{ $record->title }}</div><div class="meta">{{ $record->customer->user->name }} · {{ $record->period_label }}</div></div>
                        <x-status-badge :status="$record->status" />
                    </div>
                @empty
                    <x-empty-state icon="bi-calendar-check" title="No upcoming due dates" />
                @endforelse
            </div>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#8a97a8';
    const tooltip = { backgroundColor: '#0b2a4a', padding: 10, cornerRadius: 10, titleFont: { weight: '600' }, displayColors: false };
    @if ($revenue)
    (() => {
        const canvas = document.getElementById('revenueChart');
        const ctx = canvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, canvas.parentElement.clientHeight || 290);
        grad.addColorStop(0, '#2f80ed');
        grad.addColorStop(1, 'rgba(47, 128, 237, .25)');
        new Chart(canvas, {
            type: 'bar',
            data: { labels: @json($revenue['labels']), datasets: [{ label: 'Revenue', data: @json($revenue['values']), backgroundColor: grad, hoverBackgroundColor: '#0b2a4a', borderRadius: 8, borderSkipped: false, maxBarThickness: 30 }] },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...tooltip, callbacks: { label: (c) => '₹' + c.parsed.y.toLocaleString('en-IN') } } },
                scales: {
                    y: { beginAtZero: true, border: { display: false }, grid: { color: '#eef2f7' }, ticks: { padding: 8, callback: (v) => '₹' + (v >= 1000 ? (v / 1000) + 'k' : v) } },
                    x: { border: { display: false }, grid: { display: false } },
                },
            },
        });
    })();
    @endif
    @if ($byStatus)
    (() => {
        const labels = @json(array_keys($byStatus));
        const values = @json(array_values($byStatus));
        const colors = ['#1565c0', '#f59e0b', '#0ea5e9', '#0d9488', '#16a34a', '#6366f1', '#e11d48', '#94a3b8', '#0b2a4a', '#a855f7', '#64748b'];
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }] },
            options: { maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false }, tooltip } },
        });
        // Custom legend: colour dot, label, count.
        document.getElementById('statusLegend').innerHTML = labels.map((l, i) =>
            '<div class="donut-legend-item"><span class="dot" style="background:' + colors[i % colors.length] + '"></span><span class="lbl">' + l + '</span><span class="val">' + values[i] + '</span></div>').join('');
    })();
    @endif
</script>
@endpush
