@extends('layouts.admin')
@section('title', (string) ('Staff'))

@section('content')
<x-page-header title="Staff" subtitle="Team members who work in the admin panel.">
    @can('staff.manage')<a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Staff</a>@endcan
</x-page-header>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or email"></div>
    <div class="col-6 col-md-3"><select name="role" class="form-select"><option value="">Any role</option>@foreach ($filterRoles as $v => $l)<option value="{{ $v }}" @selected(request('role') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th class="col-sl">#</th><th>Staff member</th><th>Contact</th><th>Role &amp; department</th><th class="text-center">Open apps</th><th>Last login</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($staff as $u)
                @php($me = $u->is(auth()->user()))
                <tr @class(["is-inactive" => $u->status !== "active"])>
                    <td class="col-sl" data-label="#">{{ $staff->firstItem() + $loop->index }}</td>
                    <td data-label="Staff member">
                        <span class="staff-cell">
                            <span class="avatar staff-avatar tone-{{ crc32($u->email) % 6 }} {{ $u->status === "active" ? "is-active" : "" }}" title="{{ ucfirst($u->status) }}">{{ $u->initials() }}</span>
                            <span class="min-w-0">
                                <span class="fw-semibold text-navy d-block text-truncate">{{ $u->name }} @if ($me)<span class="badge badge-soft-secondary ms-1">You</span>@endif @if ($u->status !== "active")<span class="badge badge-soft-secondary ms-1">Inactive</span>@endif</span>
                                <small class="text-muted d-block text-truncate">{{ $u->staffProfile?->designation ?: '—' }}</small>
                            </span>
                        </span>
                    </td>
                    <td data-label="Contact" class="staff-contact stack-multi">
                        <span class="d-block text-truncate"><i class="bi bi-envelope"></i>{{ $u->email }}</span>
                        @if ($u->mobile)<small class="d-block text-muted"><i class="bi bi-telephone"></i>{{ $u->mobile }}</small>@endif
                    </td>
                    <td data-label="Role &amp; department" class="stack-multi">
                        @foreach ($u->roles as $r)<span class="badge badge-soft-primary">{{ $roles[$r->name] ?? $r->name }}</span>@endforeach
                        <small class="d-block text-muted mt-1">{{ $u->staffProfile?->department ?: 'No department' }}</small>
                    </td>
                    <td data-label="Open apps" class="text-center">
                        <a href="{{ route('admin.applications.index', ['staff' => $u->id, 'status' => 'active']) }}" class="count-pill {{ $u->open_applications_count ? 'has' : '' }}" title="Open applications assigned">{{ $u->open_applications_count }}</a>
                    </td>
                    <td data-label="Last login" class="text-nowrap" @if ($u->last_login_at) title="{{ $u->last_login_at->format('d M Y, h:i A') }}" @endif>
                        @if ($u->last_login_at){{ $u->last_login_at->diffForHumans(short: true) }}@else<span class="text-muted">Never</span>@endif
                    </td>
                    <td class="td-actions text-end text-nowrap">
                        @can('staff.manage')
                            <a href="{{ route('admin.staff.edit', $u) }}" class="btn btn-sm btn-light" title="Edit" aria-label="Edit {{ $u->name }}"><i class="bi bi-pencil"></i></a>
                            @if ($u->status === 'active' && ! $me)
                                <form method="POST" action="{{ route('admin.staff.destroy', $u) }}" class="d-inline" data-confirm="Deactivate {{ $u->name }}?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Deactivate" aria-label="Deactivate {{ $u->name }}"><i class="bi bi-person-x"></i></button></form>
                            @elseif ($u->status !== 'active')
                                <form method="POST" action="{{ route('admin.staff.activate', $u) }}" class="d-inline" data-confirm="Activate {{ $u->name }}?"
                                    data-confirm-text="They will be able to sign in to the admin panel again with their existing password." data-confirm-button="Yes, activate" data-confirm-variant="primary">@csrf<button class="btn btn-sm btn-light text-success" title="Activate" aria-label="Activate {{ $u->name }}"><i class="bi bi-person-check"></i></button></form>
                            @else
                                {{-- keeps the edit buttons lined up --}}
                                <span class="btn btn-sm btn-light invisible" aria-hidden="true"><i class="bi bi-person-x"></i></span>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="bi-person-badge" title="No staff found" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$staff" label="staff" />
</div>
@endsection
