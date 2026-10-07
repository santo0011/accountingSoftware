@extends('layouts.admin')
@section('title', (string) ('Reports'))

@section('content')
<x-page-header title="Reports" :subtitle="$from->format('d M Y').' – '.$to->format('d M Y')">
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download me-1"></i>Export CSV</button>
        <ul class="dropdown-menu dropdown-menu-end">
            @foreach (['applications', 'payments', 'leads', 'customers'] as $type)
                <li><a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => $type, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">{{ ucfirst($type) }}</a></li>
            @endforeach
        </ul>
    </div>
</x-page-header>

<form class="filter-bar row g-2 align-items-end" method="GET" data-no-live>
    <div class="col-6 col-md-3"><label class="form-label small">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control"></div>
    <div class="col-6 col-md-3"><label class="form-label small">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Apply</button></div>
    <div class="col-md-4 d-flex gap-2 flex-wrap">
        <a class="btn btn-sm btn-light" href="?from={{ now()->startOfMonth()->toDateString() }}&to={{ now()->toDateString() }}">This month</a>
        @php($fyStart = now()->month >= 4 ? now()->startOfYear()->addMonths(3) : now()->subYear()->startOfYear()->addMonths(3))
        <a class="btn btn-sm btn-light" href="?from={{ $fyStart->toDateString() }}&to={{ now()->toDateString() }}">This FY</a>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><x-stat-card label="Revenue" :value="money($summary['revenue'], false)" icon="bi-currency-rupee" color="green" /></div>
    <div class="col-6 col-lg-2"><x-stat-card label="Payments" :value="$summary['payments']" icon="bi-credit-card" /></div>
    <div class="col-6 col-lg-2"><x-stat-card label="Applications" :value="$summary['applications']" icon="bi-folder2-open" /></div>
    <div class="col-6 col-lg-2"><x-stat-card label="New customers" :value="$summary['new_customers']" icon="bi-people" color="teal" /></div>
    <div class="col-6 col-lg-2"><x-stat-card label="Leads" :value="$summary['leads']" icon="bi-person-lines-fill" color="amber" /></div>
    <div class="col-6 col-lg-2"><x-stat-card label="Lead conversion" :value="($summary['leads'] ? round($summary['converted'] / $summary['leads'] * 100) : 0).'%'" icon="bi-graph-up-arrow" color="green" /></div>
</div>

<div class="row g-4">
    <div class="col-xl-8"><div class="card h-100"><div class="card-header">Revenue by month</div><div class="card-body"><div class="chart-box"><canvas id="rev"></canvas></div></div></div></div>
    <div class="col-xl-4"><div class="card h-100"><div class="card-header">Applications by status</div><div class="card-body"><div class="chart-box"><canvas id="st"></canvas></div></div></div></div>
    <div class="col-xl-6">
        <div class="table-card h-100">
            <div class="card-header bg-white">Top services</div>
            <table class="table">
                <thead><tr><th>Service</th><th class="text-end">Applications</th><th class="text-end">Revenue (paid)</th></tr></thead>
                <tbody>
                @forelse ($topServices as $row)
                    <tr><td>{{ $row->name }}</td><td class="text-end">{{ $row->applications }}</td><td class="text-end">{{ money($row->revenue, false) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted small">No data for this period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-header">Leads by source</div><div class="card-body"><div class="chart-box" style="height:240px"><canvas id="src"></canvas></div></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-header">Leads by status</div><div class="card-body"><div class="chart-box" style="height:240px"><canvas id="lst"></canvas></div></div></div></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#6b7280';
    const palette = ['#1565c0', '#16a34a', '#f59e0b', '#0d9488', '#dc2626', '#6366f1', '#0b2a4a', '#a855f7', '#94a3b8', '#0ea5e9', '#64748b'];
    const doughnut = (id, data) => new Chart(document.getElementById(id), {
        type: 'doughnut', data: { labels: Object.keys(data), datasets: [{ data: Object.values(data), backgroundColor: palette, borderWidth: 0 }] },
        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
    });
    new Chart(document.getElementById('rev'), {
        type: 'line',
        data: { labels: @json($revenue['labels']), datasets: [{ label: 'Revenue', data: @json($revenue['values']), borderColor: '#1565c0', backgroundColor: 'rgba(21,101,192,.08)', fill: true, tension: .3 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: (v) => '₹' + Number(v).toLocaleString('en-IN') } }, x: { grid: { display: false } } } }
    });
    doughnut('st', @json($byStatus ?: ['No data' => 0]));
    doughnut('src', @json($leadSources ?: ['No data' => 0]));
    doughnut('lst', @json($leadStatuses ?: ['No data' => 0]));
</script>
@endpush
