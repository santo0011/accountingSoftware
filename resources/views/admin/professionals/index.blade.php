@extends('layouts.admin')
@section('title', (string) ('Professionals'))

@section('content')
<x-page-header title="Professionals" subtitle="CAs, CSs, lawyers and consultants who can be assigned to applications.">
    @can('professionals.manage')<a href="{{ route('admin.professionals.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Professional</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or specialization"></div>
    <div class="col-6 col-md-3"><select name="type" class="form-select"><option value="">Any type</option>@foreach (\App\Models\Professional::TYPES as $v => $l)<option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th class="col-sl">#</th><th>Professional</th><th>Type &amp; registration</th><th>Specialization</th><th class="text-center">Open apps</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($professionals as $p)
                <tr>
                    <td class="col-sl" data-label="#">{{ $professionals->firstItem() + $loop->index }}</td>
                    <td data-label="Professional" class="stack-multi td-clip" title="{{ $p->name }} · {{ $p->email }}">
                        <span class="fw-semibold text-navy d-block">{{ $p->name }}</span>
                        <small class="text-muted d-block">{{ $p->email }}@if ($p->phone) · {{ $p->phone }}@endif</small>
                    </td>
                    <td data-label="Type &amp; registration" class="stack-multi td-clip narrow" title="{{ $p->typeLabel() }} · {{ $p->registration_no }}">
                        <span class="d-block">{{ $p->typeLabel() }}</span>
                        <small class="text-muted d-block">{{ $p->registration_no ?? 'No registration no.' }}</small>
                    </td>
                    <td data-label="Specialization" class="td-clip narrow" title="{{ $p->specialization }}"><span class="d-block">{{ $p->specialization ?? '—' }}</span></td>
                    <td data-label="Open apps" class="text-center"><span class="count-pill {{ $p->open_applications_count ? 'has' : '' }}">{{ $p->open_applications_count }}</span></td>
                    <td data-label="Status" class="text-nowrap stack-multi">
                        <span class="status-dot {{ $p->status === 'active' ? 'on' : 'off' }} d-flex">{{ ucfirst($p->status) }}</span>
                        <small class="text-muted d-block mt-1">@if ($p->user)<i class="bi bi-key me-1"></i>Has login@else No login @endif</small>
                    </td>
                    <td class="td-actions text-end text-nowrap">
                        @can('professionals.manage')
                            <a href="{{ route('admin.professionals.edit', $p) }}" class="btn btn-sm btn-light" title="Edit" aria-label="Edit {{ $p->name }}"><i class="bi bi-pencil"></i></a>
                            @if ($p->status === 'active')
                                <form method="POST" action="{{ route('admin.professionals.destroy', $p) }}" class="d-inline" data-confirm="Deactivate {{ $p->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Deactivate" aria-label="Deactivate {{ $p->name }}"><i class="bi bi-person-x"></i></button></form>
                            @else
                                <span class="btn btn-sm btn-light invisible" aria-hidden="true"><i class="bi bi-person-x"></i></span>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="bi-mortarboard" title="No professionals found" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$professionals" label="professionals" />
</div>
@endsection
