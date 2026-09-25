@extends('layouts.admin')
@section('title', (string) ('Roles & Permissions'))

@section('content')
<x-page-header title="Roles & permissions" subtitle="Control what each team role can see and do.">
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Role</a>
</x-page-header>

<div class="row g-3">
    @foreach ($roles as $role)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold text-navy">{{ config("rbac.roles.{$role->name}.label", ucwords(str_replace('-', ' ', $role->name))) }}</div>
                            <code class="small">{{ $role->name }}</code>
                        </div>
                        <span class="icon-bubble sm {{ $role->name === 'super-admin' ? 'navy' : '' }}"><i class="bi bi-shield-lock"></i></span>
                    </div>
                    <div class="small text-muted mb-3">
                        {{ $role->users_count }} user(s) ·
                        @if ($role->name === 'super-admin') all permissions @else {{ $role->permissions_count }} permissions @endif
                    </div>
                    @if (in_array($role->name, $locked, true))
                        <span class="badge badge-soft-secondary"><i class="bi bi-lock me-1"></i>System role</span>
                    @else
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Edit permissions</a>
                        @if ($role->users_count === 0 && $role->name !== 'admin')
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" data-confirm="Delete this role?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger">Delete</button></form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
