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
            <thead><tr><th class="col-sl">#</th><th>Professional</th><th>Type</th><th>Registration no.</th><th>Specialization</th><th>Open apps</th><th>Login</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($professionals as $p)
                <tr>
                    <td class="col-sl" data-label="#">{{ $professionals->firstItem() + $loop->index }}</td>
                    <td data-label="Professional"><span class="fw-semibold text-navy">{{ $p->name }}</span><br><small class="text-muted">{{ $p->email }} {{ $p->phone }}</small></td>
                    <td data-label="Type">{{ $p->typeLabel() }}</td>
                    <td data-label="Registration no.">{{ $p->registration_no ?? '—' }}</td>
                    <td data-label="Specialization">{{ $p->specialization ?? '—' }}</td>
                    <td data-label="Open apps">{{ $p->open_applications_count }}</td>
                    <td data-label="Login">@if ($p->user)<i class="bi bi-check-circle-fill text-green" title="Has panel access"></i>@else<span class="text-muted">—</span>@endif</td>
                    <td data-label="Status"><span class="badge badge-soft-{{ $p->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($p->status) }}</span></td>
                    <td class="td-actions text-end text-nowrap">
                        @can('professionals.manage')
                            <a href="{{ route('admin.professionals.edit', $p) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            @if ($p->status === 'active')
                                <form method="POST" action="{{ route('admin.professionals.destroy', $p) }}" class="d-inline" data-confirm="Deactivate {{ $p->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-person-x"></i></button></form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><x-empty-state icon="bi-mortarboard" title="No professionals found" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$professionals" label="professionals" />
</div>
@endsection
