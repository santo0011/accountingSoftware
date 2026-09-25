@extends('layouts.admin')
@section('title', (string) ($professional->exists ? 'Edit Professional' : 'Add Professional'))

@section('content')
<x-page-header :title="$professional->exists ? 'Edit '.$professional->name : 'Add professional'" :back="route('admin.professionals.index')" />

<form method="POST" action="{{ $professional->exists ? route('admin.professionals.update', $professional) : route('admin.professionals.store') }}" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($professional->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Full name" :value="$professional->name" required col="col-md-6 mb-3" />
        <x-form.select name="professional_type" label="Professional type" :options="\App\Models\Professional::TYPES" :value="$professional->professional_type" placeholder="Select" required col="col-md-6 mb-3" />
        <x-form.input name="email" type="email" label="Email" :value="$professional->email" col="col-md-6 mb-3" />
        <x-form.input name="phone" label="Phone" :value="$professional->phone" prepend="+91" maxlength="10" col="col-md-6 mb-3" />
        <x-form.input name="registration_no" label="Registration / licence no." :value="$professional->registration_no" placeholder="e.g. ICAI M.No., Bar Council no." col="col-md-6 mb-3" />
        <x-form.input name="specialization" label="Specialization" :value="$professional->specialization" col="col-md-6 mb-3" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$professional->status" col="col-md-4 mb-3" />

        <div class="col-12"><div class="divider"></div></div>
        @if ($professional->user)
            <div class="col-12 mb-2 small"><i class="bi bi-check-circle-fill text-green me-1"></i>Has admin-panel login ({{ $professional->user->email }}).</div>
            <input type="hidden" name="create_login" value="1">
        @else
            <div class="col-12 mb-2">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="create_login" value="1" id="create_login" @checked(old('create_login'))
                        onchange="document.getElementById('loginBox').classList.toggle('d-none', !this.checked)">
                    <label class="form-check-label" for="create_login">Give this professional a login to the admin panel</label>
                </div>
                <div class="form-text">They'll only see applications assigned to them.</div>
            </div>
        @endif
        <div id="loginBox" class="col-12 {{ $professional->user || old('create_login') ? '' : 'd-none' }}">
            <div class="row">
                <x-form.select name="login_role" label="Access role" :options="$loginRoles" :value="$professional->user?->getRoleNames()->first() ?? 'tax-professional'" col="col-md-4 mb-3" />
                <x-form.input name="password" type="password" label="{{ $professional->user ? 'New password (optional)' : 'Password' }}" autocomplete="new-password" col="col-md-4 mb-3" />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" col="col-md-4 mb-3" />
            </div>
        </div>
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
