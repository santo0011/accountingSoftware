@extends('layouts.admin')
@section('title', (string) ('Services'))

@section('content')
<x-page-header title="Services" subtitle="Manage pricing, documents, application fields and website content for each service.">
    @can('services.manage')<a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Service</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search services"></div>
    <div class="col-6 col-md-3"><select name="category" class="form-select"><option value="">All categories</option>@foreach ($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="billing" class="form-select"><option value="">Any type</option><option value="one_time" @selected(request('billing') === 'one_time')>One-time</option><option value="recurring" @selected(request('billing') === 'recurring')>Recurring</option></select></div>
    <div class="col-12 col-md-2"><select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Disabled</option></select></div>
</form>

<div class="table-card">
    @if ($services->isEmpty())
        <x-empty-state icon="bi-briefcase" title="No services found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Service</th><th>Category &amp; type</th><th class="text-end">Price</th><th class="text-center">Apps</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($services as $s)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $services->firstItem() + $loop->index }}</td>
                        <td data-label="Service" title="{{ $s->name }}"><span class="svc-cell"><span class="svc-thumb"><img src="{{ $s->imageUrl() }}" alt="" loading="lazy" width="72" height="48"><i class="bi {{ $s->iconClass() }}"></i></span><span class="min-w-0"><span class="fw-semibold text-navy d-block text-truncate">{{ $s->name }} @if ($s->is_featured)<i class="bi bi-star-fill text-warning small" title="Featured"></i>@endif</span><small class="text-muted d-block text-truncate">/services/{{ $s->slug }}</small></span></span></td>
                        <td data-label="Category &amp; type" class="stack-multi td-clip narrow" title="{{ $s->category?->name }}"><span class="d-block">{{ $s->category?->name }}</span><span class="d-block mt-1">@if ($s->isRecurring())<span class="badge badge-soft-teal">{{ $s->intervalLabel() }}</span>@else<span class="badge badge-soft-secondary">One-time</span>@endif</span></td>
                        <td data-label="Price" class="text-end text-nowrap stack-multi"><strong class="d-block">{{ money($s->effectivePrice(), false) }}</strong>@if ($s->hasDiscount())<span class="price-old small d-block">{{ money($s->price, false) }}</span>@endif</td>
                        <td data-label="Apps" class="text-center"><a href="{{ route('admin.applications.index', ['service' => $s->id]) }}" class="count-pill {{ $s->applications_count ? 'has' : '' }}" title="Applications for this service">{{ $s->applications_count }}</a></td>
                        <td data-label="Status"><span class="status-dot {{ $s->status ? 'on' : 'off' }}">{{ $s->status ? 'Active' : 'Disabled' }}</span></td>
                        <td class="td-actions text-end text-nowrap">
                            <a href="{{ route('site.services.show', $s->slug) }}" target="_blank" class="btn btn-sm btn-light" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                            @can('services.manage')
                                <a href="{{ route('admin.services.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                                @if ($s->applications_count)
                                    {{-- Services with applications are kept for history: the button disables them instead --}}
                                    @if ($s->status)
                                        <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="d-inline" data-confirm="Disable this service?"
                                            data-confirm-text="It has {{ $s->applications_count }} {{ \Illuminate\Support\Str::plural('application', $s->applications_count) }}, so it can’t be deleted. It will be hidden from the website instead — you can turn it back on from Edit."
                                            data-confirm-button="Yes, disable">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Disable (has applications)" aria-label="Disable {{ $s->name }}"><i class="bi bi-slash-circle"></i></button></form>
                                    @else
                                        <span class="btn btn-sm btn-light disabled" title="Has applications — can’t be deleted" aria-hidden="true"><i class="bi bi-trash"></i></span>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="d-inline" data-confirm="Delete this service?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Delete" aria-label="Delete {{ $s->name }}"><i class="bi bi-trash"></i></button></form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$services" label="services" />
    @endif
</div>
@endsection
