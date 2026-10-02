@extends('layouts.auth')
@section('title', (string) ('Create your account'))

@section('content')
<div class="auth-card wide">
    <h1 class="h3 mb-4">Create your free account</h1>

    <form method="POST" action="{{ route('register') }}" novalidate data-register-form>
        @csrf
        <div class="row">
            <x-form.input name="name" label="Full name" required autofocus autocomplete="name" col="col-md-6 mb-3" />
            <x-form.input name="mobile" label="Mobile number" type="tel" required prepend="+91" inputmode="numeric" maxlength="10" autocomplete="tel-national" col="col-md-6 mb-3" />
            <x-form.input name="email" label="Email address" type="email" required autocomplete="email" col="col-12 mb-3" />
            <x-form.input name="business_name" label="Business name" placeholder="Optional" col="col-md-6 mb-3" />
            <x-form.select name="business_type" label="Business type" :options="$businessTypes" placeholder="Select (optional)" col="col-md-6 mb-3" />
            <x-form.input name="password" label="Password" type="password" required autocomplete="new-password" col="col-md-6 mb-3" />
            <x-form.input name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" col="col-md-6 mb-3" />
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" @checked(old('terms'))>
            <label class="form-check-label small" for="terms">
                I agree to the <a href="{{ route('site.terms') }}" target="_blank">Terms & Conditions</a> and <a href="{{ route('site.privacy') }}" target="_blank">Privacy Policy</a>.
            </label>
            @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary auth-submit" data-submit>
            <span class="auth-submit-label">Create Account</span>
            <i class="bi bi-arrow-right auth-submit-icon"></i>
            <span class="spinner-border spinner-border-sm auth-submit-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <div class="divider"></div>
    <p class="auth-alt mt-0">Already have an account? <a href="{{ route('login') }}" class="auth-link">Login</a></p>
</div>
@endsection

@push('scripts')
<script>
    // Show a loader on the button while the account is created, and block double submits.
    (function () {
        var form = document.querySelector('[data-register-form]');
        form.addEventListener('submit', function (e) {
            var btn = form.querySelector('[data-submit]');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.classList.add('loading');
            btn.disabled = true;
            btn.querySelector('.auth-submit-label').textContent = 'Creating account…';
        });
        // Coming back via the browser Back button: reset the button.
        window.addEventListener('pageshow', function () {
            var btn = form.querySelector('[data-submit]');
            btn.classList.remove('loading');
            btn.disabled = false;
            btn.querySelector('.auth-submit-label').textContent = 'Create Account';
        });
    })();
</script>
@endpush
