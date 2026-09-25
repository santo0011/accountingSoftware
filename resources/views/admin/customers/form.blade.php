@extends('layouts.admin')
@section('title', (string) ($customer->exists ? 'Edit Customer' : 'Add Customer'))

@section('content')
<x-page-header :title="$customer->exists ? 'Edit '.$customer->user->name : 'Add customer'" :back="$customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index')" />

<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($customer->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Full name" :value="$customer->user?->name" required col="col-md-6 mb-3" />
        <x-form.input name="email" type="email" label="Email" :value="$customer->user?->email" required col="col-md-6 mb-3" />
        <x-form.input name="mobile" label="Mobile" :value="$customer->user?->mobile" prepend="+91" maxlength="10" col="col-md-4 mb-3" />
        <x-form.input name="pan" label="PAN" :value="$customer->pan" maxlength="10" col="col-md-4 mb-3" />
        @if ($customer->exists)
            <x-form.select name="status" label="Account status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked']" :value="$customer->user->status" col="col-md-4 mb-3" />
        @else
            <x-form.input name="source" label="Source" value="walk_in" col="col-md-4 mb-3" />
        @endif
        <x-form.input name="address" label="Address" :value="$customer->address" col="col-md-12 mb-3" />
        <x-form.input name="city" label="City" :value="$customer->city" col="col-md-4 mb-3" />
        <x-form.input name="state" label="State" :value="$customer->state" col="col-md-4 mb-3" />
        <x-form.input name="pincode" label="PIN code" :value="$customer->pincode" maxlength="6" col="col-md-4 mb-3" />
        @unless ($customer->exists)
            <div class="col-12"><div class="divider"></div><div class="small-caps text-muted mb-2">Primary business (optional)</div></div>
            <x-form.input name="business_name" label="Business name" col="col-md-6 mb-3" />
            <x-form.select name="business_type" label="Business type" :options="\App\Models\Business::TYPES" placeholder="Select" col="col-md-6 mb-3" />
            <x-form.check name="send_invite" label="Email the customer a link to set their password" :checked="true" col="col-12" />
        @endunless
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">{{ $customer->exists ? 'Save Changes' : 'Create Customer' }}</button></div>
</form>
@endsection
