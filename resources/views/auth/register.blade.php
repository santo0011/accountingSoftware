@extends('layouts.auth')
@section('title', (string) ('Create your account'))

@section('content')
<div class="auth-card wide">
    <h1 class="h3 mb-1">Create your free account</h1>
    <p class="text-muted mb-4">Apply for services, upload documents and track progress — all in one place.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="row">
            <x-form.input name="name" label="Full name" required autofocus autocomplete="name" col="col-md-6 mb-3" />
            <x-form.input name="mobile" label="Mobile number" type="tel" required prepend="+91" inputmode="numeric" maxlength="10" autocomplete="tel-national" col="col-md-6 mb-3" />
            <x-form.input name="email" label="Email address" type="email" required autocomplete="email" col="col-12 mb-3" />
            <x-form.input name="business_name" label="Business name" placeholder="Optional" col="col-md-6 mb-3" />
            <x-form.select name="business_type" label="Business type" :options="$businessTypes" placeholder="Select (optional)" col="col-md-6 mb-3" />
            <x-form.input name="password" label="Password" type="password" required autocomplete="new-password" help="At least 8 characters with letters and numbers." col="col-md-6 mb-3" />
            <x-form.input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" col="col-md-6 mb-3" />
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" @checked(old('terms'))>
            <label class="form-check-label small" for="terms">
                I agree to the <a href="{{ route('site.terms') }}" target="_blank">Terms & Conditions</a> and <a href="{{ route('site.privacy') }}" target="_blank">Privacy Policy</a>.
            </label>
            @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-cta w-100 btn-lg">Create Account</button>
    </form>

    <div class="divider"></div>
    <p class="text-center mb-0 small">Already have an account? <a href="{{ route('login') }}" class="fw-semibold">Login</a></p>
</div>
@endsection
