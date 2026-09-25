@extends('layouts.admin')
@section('title', (string) ($user->exists ? 'Edit Staff' : 'Add Staff'))

@section('content')
<x-page-header :title="$user->exists ? 'Edit '.$user->name : 'Add staff member'" :back="route('admin.staff.index')" />

<form method="POST" action="{{ $user->exists ? route('admin.staff.update', $user) : route('admin.staff.store') }}" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Full name" :value="$user->name" required col="col-md-6 mb-3" />
        <x-form.input name="email" type="email" label="Email (login)" :value="$user->email" required col="col-md-6 mb-3" />
        <x-form.input name="mobile" label="Phone" :value="$user->mobile" prepend="+91" maxlength="10" col="col-md-4 mb-3" />
        <x-form.select name="role" label="Role" :options="$roles" :value="$user->exists ? $user->getRoleNames()->first() : 'staff'" required col="col-md-4 mb-3" />
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$user->status" required col="col-md-4 mb-3" />
        <x-form.input name="employee_code" label="Employee code" :value="$user->staffProfile?->employee_code" col="col-md-3 mb-3" />
        <x-form.select name="department" label="Department" :options="array_combine(\App\Models\StaffProfile::DEPARTMENTS, \App\Models\StaffProfile::DEPARTMENTS)" :value="$user->staffProfile?->department" placeholder="Select" col="col-md-3 mb-3" />
        <x-form.input name="designation" label="Designation" :value="$user->staffProfile?->designation" col="col-md-3 mb-3" />
        <x-form.input name="joined_on" type="date" label="Joined on" :value="$user->staffProfile?->joined_on?->format('Y-m-d')" col="col-md-3 mb-3" />
        <div class="col-12"><div class="divider"></div><div class="small-caps text-muted mb-2">{{ $user->exists ? 'Reset password (optional)' : 'Password' }}</div></div>
        <x-form.input name="password" type="password" label="Password" :required="! $user->exists" autocomplete="new-password" col="col-md-6 mb-3" />
        <x-form.input name="password_confirmation" type="password" label="Confirm password" :required="! $user->exists" autocomplete="new-password" col="col-md-6 mb-3" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
