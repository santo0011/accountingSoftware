@extends('layouts.admin')
@section('title', (string) ('Customers'))

@section('content')
<x-page-header title="Customers" subtitle="All registered customers and their businesses.">
    @can('customers.create')<a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Customer</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, email, mobile, customer ID, business, GSTIN"></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option>@foreach (['active', 'inactive', 'blocked'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><input type="text" name="state" value="{{ request('state') }}" class="form-control" placeholder="State"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Search</button></div>
</form>

<div class="table-card">
    @if ($customers->isEmpty())
        <x-empty-state icon="bi-people" title="No customers found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Customer</th><th>Contact</th><th>Business</th><th class="text-center">Apps</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach ($customers as $c)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $customers->firstItem() + $loop->index }}</td>
                        <td data-label="Customer"><a href="{{ route('admin.customers.show', $c) }}" class="d-flex align-items-center gap-2 text-nowrap"><span class="avatar sm">{{ $c->user->initials() }}</span><span><span class="fw-semibold d-block">{{ $c->user->name }}</span><small class="text-muted">{{ $c->customer_code }}</small></span></a></td>
                        <td data-label="Contact" class="stack-multi td-clip" title="{{ $c->user->email }}">
                            <span class="d-block">{{ $c->user->email }}</span>
                            <small class="text-muted d-block">{{ $c->user->mobile }}</small>
                        </td>
                        <td data-label="Business" class="td-clip narrow" title="{{ $c->primaryBusiness?->name }}"><span class="d-block">{{ $c->primaryBusiness?->name ?? '—' }}</span></td>
                        <td data-label="Apps" class="text-center"><span class="count-pill {{ $c->applications_count ? 'has' : '' }}">{{ $c->applications_count }}</span></td>
                        <td data-label="Status"><span class="status-dot {{ $c->user->status === 'active' ? 'on' : 'off' }}">{{ ucfirst($c->user->status) }}</span></td>
                        <td class="td-actions text-end"><a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-cx-view text-nowrap"><i class="bi bi-eye me-1"></i>View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$customers" label="customers" />
    @endif
</div>
@endsection
