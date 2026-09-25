@extends('layouts.admin')
@section('title', (string) ('My Profile'))

@section('content')
<x-page-header title="My profile" :subtitle="$user->email" />

<div class="row g-4">
    <div class="col-lg-6">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="card" novalidate>
            @csrf @method('PUT')
            <div class="card-header">Profile</div>
            <div class="card-body">
                <x-form.input name="name" label="Name" :value="$user->name" required />
                <x-form.input name="mobile" label="Mobile" :value="$user->mobile" prepend="+91" maxlength="10" />
                <dl class="dl-grid mt-3">
                    <dt>Role</dt><dd>{{ $user->getRoleNames()->map(fn ($r) => config("rbac.roles.$r.label", $r))->implode(', ') }}</dd>
                    @if ($user->staffProfile)<dt>Department</dt><dd>{{ $user->staffProfile->department ?? '—' }}</dd>@endif
                    <dt>Last login</dt><dd>{{ $user->last_login_at?->format('d M Y, h:i A') ?? '—' }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
    <div class="col-lg-6">
        <form method="POST" action="{{ route('admin.profile.password') }}" class="card" novalidate>
            @csrf @method('PUT')
            <div class="card-header">Change password</div>
            <div class="card-body">
                @php($pe = $errors->password)
                @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
                    <div class="mb-3">
                        <label class="form-label required" for="{{ $field }}">{{ $label }}</label>
                        <input type="password" name="{{ $field }}" id="{{ $field }}" class="form-control {{ $pe->has($field) ? 'is-invalid' : '' }}" required>
                        @if ($pe->has($field))<div class="invalid-feedback">{{ $pe->first($field) }}</div>@endif
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary">Change Password</button></div>
        </form>
    </div>
</div>
@endsection
