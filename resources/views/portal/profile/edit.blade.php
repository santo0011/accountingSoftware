@extends('layouts.portal')
@section('title', (string) ('My Profile'))

@section('content')
<x-page-header title="My Profile" :subtitle="'Customer ID: '.$customer->customer_code" />

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profile</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#businesses" type="button">Businesses <span class="badge badge-soft-secondary">{{ $businesses->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link {{ $errors->password->any() ? 'text-danger' : '' }}" data-bs-toggle="tab" data-bs-target="#security" type="button">Password</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="profile">
        <form method="POST" action="{{ route('portal.profile.update') }}" class="card" novalidate>
            @csrf @method('PUT')
            <div class="card-body row">
                <x-form.input name="name" label="Full name" :value="$user->name" required col="col-md-6 mb-3" />
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    <div class="form-text">Contact support to change your email.</div>
                </div>
                <x-form.input name="mobile" label="Mobile" :value="$user->mobile" required prepend="+91" maxlength="10" col="col-md-6 mb-3" />
                <x-form.input name="alt_phone" label="Alternate phone" :value="$customer->alt_phone" col="col-md-6 mb-3" />
                <x-form.input name="pan" label="PAN" :value="$customer->pan" maxlength="10" class="text-uppercase" col="col-md-4 mb-3" />
                <x-form.input name="city" label="City" :value="$customer->city" col="col-md-4 mb-3" />
                <x-form.input name="state" label="State" :value="$customer->state" col="col-md-4 mb-3" />
                <x-form.input name="address" label="Address" :value="$customer->address" col="col-md-9 mb-3" />
                <x-form.input name="pincode" label="PIN code" :value="$customer->pincode" maxlength="6" col="col-md-3 mb-3" />
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Changes</button></div>
        </form>
    </div>

    <div class="tab-pane fade" id="businesses">
        @foreach ($businesses as $business)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $business->name }} @if ($business->is_primary)<span class="badge badge-soft-primary ms-1">Primary</span>@endif</span>
                    <form method="POST" action="{{ route('portal.businesses.destroy', $business) }}" data-confirm="Remove this business?">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" data-no-lock><i class="bi bi-trash"></i></button>
                    </form>
                </div>
                <form method="POST" action="{{ route('portal.businesses.update', $business) }}" class="card-body row">
                    @csrf @method('PUT')
                    @include('portal.profile.partials.business-fields', ['b' => $business])
                    <div class="col-12 text-end"><button class="btn btn-sm btn-primary">Update</button></div>
                </form>
            </div>
        @endforeach
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-1"></i> Add a business</div>
            <form method="POST" action="{{ route('portal.businesses.store') }}" class="card-body row">
                @csrf
                @include('portal.profile.partials.business-fields', ['b' => null])
                <div class="col-12 text-end"><button class="btn btn-cta">Add Business</button></div>
            </form>
        </div>
    </div>

    <div class="tab-pane fade" id="security">
        <form method="POST" action="{{ route('portal.profile.password') }}" class="card" style="max-width:560px" novalidate>
            @csrf @method('PUT')
            <div class="card-body">
                @php($pe = $errors->password)
                @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
                    <div class="mb-3">
                        <label class="form-label required" for="{{ $field }}">{{ $label }}</label>
                        <input type="password" name="{{ $field }}" id="{{ $field }}" class="form-control {{ $pe->has($field) ? 'is-invalid' : '' }}" required autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}">
                        @if ($pe->has($field))<div class="invalid-feedback">{{ $pe->first($field) }}</div>@endif
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary">Change Password</button></div>
        </form>
    </div>
</div>

@if ($errors->password->any())
    @push('scripts')<script>bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#security"]')).show();</script>@endpush
@elseif ($errors->business->any())
    @push('scripts')<script>bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#businesses"]')).show();</script>@endpush
@endif
@endsection
