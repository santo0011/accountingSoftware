@extends('layouts.admin')
@section('title', (string) ('Applications'))

@section('content')
<x-page-header title="Applications" subtitle="Every service request, from submission to completion.">
    @can('applications.create')<a href="{{ route('admin.applications.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Application</a>@endcan
</x-page-header>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-sm {{ ! request('status') ? 'btn-primary' : 'btn-light' }}">All <span class="opacity-75">{{ $counts->sum() }}</span></a>
    @foreach ($statuses as $value => $label)
        @continue(! ($counts[$value] ?? 0))
        <a href="{{ route('admin.applications.index', ['status' => $value]) }}" class="btn btn-sm {{ request('status') === $value ? 'btn-primary' : 'btn-light' }}">{{ $label }} <span class="opacity-75">{{ $counts[$value] }}</span></a>
    @endforeach
</div>

<form class="filter-bar row g-2 align-items-end" method="GET">
    <div class="col-md-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="App no., customer, email, mobile"></div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>All active</option>
            @foreach ($statuses as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select>
    </div>
    <div class="col-6 col-md-2">
        <select name="service" class="form-select"><option value="">Any service</option>@foreach ($services as $id => $name)<option value="{{ $id }}" @selected(request('service') == $id)>{{ $name }}</option>@endforeach</select>
    </div>
    <div class="col-6 col-md-2">
        <select name="staff" class="form-select"><option value="">Any staff</option><option value="unassigned" @selected(request('staff') === 'unassigned')>Unassigned</option>
            @foreach ($staff as $id => $name)<option value="{{ $id }}" @selected(request('staff') == $id)>{{ $name }}</option>@endforeach</select>
    </div>
    <div class="col-6 col-md-1">
        <select name="payment" class="form-select"><option value="">Payment</option>@foreach (\App\Enums\PaymentStatus::options() as $v => $l)<option value="{{ $v }}" @selected(request('payment') === $v)>{{ $l }}</option>@endforeach</select>
    </div>
    <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" data-no-lock>Filter</button><a href="{{ route('admin.applications.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a></div>
</form>

<div class="table-card">
    @if ($applications->isEmpty())
        <x-empty-state icon="bi-folder2-open" title="No applications match your filters" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Application</th><th>Customer</th><th>Service</th><th>Assigned</th><th class="text-end">Amount &amp; payment</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $applications->firstItem() + $loop->index }}</td>
                        <td data-label="Application" class="text-nowrap stack-multi">
                            <a href="{{ route('admin.applications.show', $app) }}" class="fw-semibold d-block">{{ $app->application_no }}</a>
                            <small class="text-muted d-block">{{ $app->created_at->format('d M Y') }}</small>
                        </td>
                        <td data-label="Customer" class="stack-multi td-clip narrow" title="{{ $app->customer->user->name }}">
                            <span class="d-block">{{ $app->customer->user->name }}</span>
                            <small class="text-muted d-block">{{ $app->customer->user->mobile }}</small>
                        </td>
                        <td data-label="Service" class="td-clip wide" title="{{ $app->service->name }}"><span class="d-block">{{ $app->service->name }}</span></td>
                        <td data-label="Assigned" class="text-nowrap">{!! $app->staff ? e($app->staff->name) : '<span class="badge badge-soft-warning">Unassigned</span>' !!}</td>
                        <td data-label="Amount &amp; payment" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($app->total) }}</span>
                            <span class="d-block mt-1"><x-status-badge :status="$app->payment_status" /></span>
                        </td>
                        <td data-label="Status" class="text-nowrap"><x-status-badge :status="$app->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$applications" label="applications" />
    @endif
</div>
@endsection
