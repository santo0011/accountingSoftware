@extends('layouts.admin')
@section('title', (string) ('Dashboard'))

@section('content')
<x-page-header title="Dashboard" :subtitle="'Good '.(now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')).', '.\Illuminate\Support\Str::before(auth()->user()->name, ' ').'. Here is today\'s overview.'">
    @can('applications.create')<a href="{{ route('admin.applications.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Application</a>@endcan
    @can('leads.create')<a href="{{ route('admin.leads.create') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1"></i>Add Lead</a>@endcan
</x-page-header>

@if ($pendingVerification)
    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-hourglass-split me-1"></i><strong>{{ $pendingVerification }}</strong> customer payment(s) are waiting for verification.</span>
        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-primary">Verify now</a>
    </div>
@endif

<div class="row g-3 mb-4">
    @php
        $cards = [
        ['Total customers', number_format($stats['customers']), 'bi-people', '', route('admin.customers.index'), '+'.$stats['new_customers'].' this month', 'customers.view'],
        ['New leads', $stats['new_leads'], 'bi-person-lines-fill', 'teal', route('admin.leads.index', ['status' => 'new']), null, 'leads.view'],
        ['Active applications', $stats['active_applications'], 'bi-folder2-open', '', route('admin.applications.index', ['status' => 'active']), null, 'applications.view'],
        ['Completed applications', $stats['completed_applications'], 'bi-patch-check', 'green', route('admin.applications.index', ['status' => 'completed']), null, 'applications.view'],
        ['Pending documents', $stats['pending_documents'], 'bi-file-earmark-arrow-up', 'amber', route('admin.documents.index'), null, 'documents.view'],
        ['Pending payments', $stats['pending_payments'], 'bi-hourglass-split', 'red', route('admin.applications.index', ['payment' => 'pending']), null, 'payments.view'],
        ['Revenue this month', money($stats['monthly_revenue'], false), 'bi-currency-rupee', 'green', route('admin.payments.index', ['status' => 'paid']), null, 'payments.view'],
        ['Upcoming compliance (30d)', $stats['upcoming_compliance'], 'bi-calendar-event', 'teal', route('admin.compliance.index'), $overdueCompliance ? $overdueCompliance.' overdue' : null, 'compliance.view'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color, $href, $hint, $perm])
        @can($perm)
            <div class="col-6 col-lg-3"><x-stat-card :label="$label" :value="$value" :icon="$icon" :color="$color" :href="$href" :hint="$hint" /></div>
        @endcan
    @endforeach
</div>

<div class="row g-4 mb-4">
    @if ($revenue)
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">Revenue — last 12 months <span class="small text-muted fw-normal">Total {{ money(array_sum($revenue['values']), false) }}</span></div>
                <div class="card-body"><div class="chart-box"><canvas id="revenueChart" aria-label="Revenue chart"></canvas></div></div>
            </div>
        </div>
    @endif
    <div class="{{ $revenue ? 'col-xl-4' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-header">Applications by status</div>
            <div class="card-body">
                @if ($byStatus)
                    <div class="chart-box" style="height:250px"><canvas id="statusChart" aria-label="Applications by status"></canvas></div>
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
    Chart.defaults.color = '#6b7280';
    @if ($revenue)
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: { labels: @json($revenue['labels']), datasets: [{ label: 'Revenue', data: @json($revenue['values']), backgroundColor: '#1565c0', borderRadius: 6, maxBarThickness: 36 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => '₹' + c.parsed.y.toLocaleString('en-IN') } } },
            scales: { y: { beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { callback: (v) => '₹' + Number(v).toLocaleString('en-IN') } }, x: { grid: { display: false } } } }
    });
    @endif
    @if ($byStatus)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: @json(array_keys($byStatus)), datasets: [{ data: @json(array_values($byStatus)), backgroundColor: ['#1565c0', '#f59e0b', '#0ea5e9', '#0d9488', '#16a34a', '#6366f1', '#dc2626', '#94a3b8', '#0b2a4a', '#a855f7', '#64748b'], borderWidth: 0 }] },
        options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } } }
    });
    @endif
</script>
@endpush
