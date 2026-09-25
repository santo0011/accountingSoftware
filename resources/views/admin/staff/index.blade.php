@extends('layouts.admin')
@section('title', (string) ('Staff'))

@section('content')
<x-page-header title="Staff" subtitle="Team members who work in the admin panel.">
    @can('staff.manage')<a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Staff</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or email"></div>
    <div class="col-6 col-md-3"><select name="role" class="form-select"><option value="">Any role</option>@foreach ($roles as $v => $l)<option value="{{ $v }}" @selected(request('role') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th>Name</th><th>Contact</th><th>Role</th><th>Department</th><th>Open applications</th><th>Last login</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($staff as $u)
                <tr>
                    <td data-label="Name"><span class="d-inline-flex align-items-center gap-2"><span class="avatar sm">{{ $u->initials() }}</span><span><span class="fw-semibold text-navy d-block">{{ $u->name }}</span><small class="text-muted">{{ $u->staffProfile?->designation }}</small></span></span></td>
                    <td data-label="Contact">{{ $u->email }}<br><small class="text-muted">{{ $u->mobile }}</small></td>
                    <td data-label="Role">@foreach ($u->roles as $r)<span class="badge badge-soft-primary">{{ $roles[$r->name] ?? $r->name }}</span>@endforeach</td>
                    <td data-label="Department">{{ $u->staffProfile?->department ?? '—' }}</td>
                    <td data-label="Open applications"><a href="{{ route('admin.applications.index', ['staff' => $u->id, 'status' => 'active']) }}">{{ $u->open_applications_count }}</a></td>
                    <td data-label="Last login">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td data-label="Status"><span class="badge badge-soft-{{ $u->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($u->status) }}</span></td>
                    <td class="td-actions text-end text-nowrap">
                        @can('staff.manage')
                            <a href="{{ route('admin.staff.edit', $u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            @if ($u->status === 'active' && ! $u->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.staff.destroy', $u) }}" class="d-inline" data-confirm="Deactivate {{ $u->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Deactivate"><i class="bi bi-person-x"></i></button></form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty-state icon="bi-person-badge" title="No staff found" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $staff->total() }} staff</span>{{ $staff->links() }}</div>
</div>
@endsection
