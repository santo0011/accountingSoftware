@extends('layouts.admin')
@section('title', (string) ('Services'))

@section('content')
<x-page-header title="Services" subtitle="Manage pricing, documents, application fields and website content for each service.">
    @can('services.manage')<a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Service</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search services"></div>
    <div class="col-6 col-md-3"><select name="category" class="form-select"><option value="">All categories</option>@foreach ($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="billing" class="form-select"><option value="">Any type</option><option value="one_time" @selected(request('billing') === 'one_time')>One-time</option><option value="recurring" @selected(request('billing') === 'recurring')>Recurring</option></select></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Disabled</option></select></div>
    <div class="col-6 col-md-1"><button class="btn btn-primary w-100" data-no-lock>Go</button></div>
</form>

<div class="table-card">
    @if ($services->isEmpty())
        <x-empty-state icon="bi-briefcase" title="No services found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th>Service</th><th>Category</th><th>Price</th><th>Type</th><th>Applications</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($services as $s)
                    <tr>
                        <td data-label="Service"><span class="d-inline-flex align-items-center gap-2"><i class="bi {{ $s->iconClass() }} text-brand fs-5"></i><span><span class="fw-semibold text-navy">{{ $s->name }}</span> @if ($s->is_featured)<i class="bi bi-star-fill text-warning small" title="Featured"></i>@endif<br><small class="text-muted">/services/{{ $s->slug }}</small></span></span></td>
                        <td data-label="Category">{{ $s->category?->name }}</td>
                        <td data-label="Price">@if ($s->hasDiscount())<span class="price-old small">{{ money($s->price, false) }}</span><br>@endif<strong>{{ money($s->effectivePrice(), false) }}</strong></td>
                        <td data-label="Type">@if ($s->isRecurring())<span class="badge badge-soft-teal">{{ $s->intervalLabel() }}</span>@else<span class="badge badge-soft-secondary">One-time</span>@endif</td>
                        <td data-label="Applications">{{ $s->applications_count }}</td>
                        <td data-label="Status"><span class="badge badge-soft-{{ $s->status ? 'success' : 'secondary' }}">{{ $s->status ? 'Active' : 'Disabled' }}</span></td>
                        <td class="td-actions text-end text-nowrap">
                            <a href="{{ route('site.services.show', $s->slug) }}" target="_blank" class="btn btn-sm btn-light" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                            @can('services.manage')
                                <a href="{{ route('admin.services.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="d-inline" data-confirm="Delete (or disable) this service?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $services->total() }} services</span>{{ $services->links() }}</div>
    @endif
</div>
@endsection
