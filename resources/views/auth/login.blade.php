@extends('layouts.auth')
@section('title', (string) ('Login'))

{{-- One form for every user. After login /dashboard sends each account to its own area by user type and role. --}}
@section('content')
<div class="auth-card">
    <div class="auth-head">
        <h1>Welcome back</h1>
        <p>Sign in to your {{ setting('company_name') }} account to continue.</p>
    </div>

    @if (session('status'))
        <div class="auth-alert success" role="status"><i class="bi bi-check-circle-fill"></i><span>{{ session('status') }}</span></div>
    @endif

    @if ($errors->any())
        <div class="auth-alert error" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ $errors->first('login') ?: $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate data-auth-form>
        @csrf
        {{-- Customers: mobile number (default). Admin / staff: email, via the link below the form. --}}
        @php($staff = str_contains((string) old('login'), '@') || request()->has('staff'))
        <div class="auth-field">
            <label for="login" class="form-label" data-login-label>{{ $staff ? 'Work email' : 'Mobile number' }}</label>
            <div class="auth-input {{ $staff ? '' : 'has-prefix' }}" data-login-wrap>
                <i class="bi {{ $staff ? 'bi-envelope-fill' : 'bi-phone-fill' }}" data-login-icon></i>
                <span class="auth-prefix" data-login-prefix @if ($staff) hidden @endif>+91</span>
                <input type="{{ $staff ? 'email' : 'tel' }}" name="login" id="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror"
                    required autofocus autocomplete="username"
                    inputmode="{{ $staff ? 'email' : 'numeric' }}" maxlength="{{ $staff ? 191 : 14 }}"
                    placeholder="{{ $staff ? 'you@company.com' : '10-digit mobile number' }}"
                    data-required-msg="{{ $staff ? 'Please enter your email address.' : 'Please enter your mobile number.' }}">
            </div>
            <div class="auth-error" data-error-for="login" hidden></div>
        </div>

        <div class="auth-field">
            <div class="d-flex justify-content-between align-items-baseline">
                <label for="password" class="form-label">Password</label>
                <a href="{{ route('password.request') }}" class="auth-link small">Forgot password?</a>
            </div>
            <div class="auth-input">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                    required autocomplete="current-password" placeholder="Enter your password"
                    data-required-msg="Please enter your password.">
                <button type="button" class="auth-eye" data-toggle-password="password" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button>
            </div>
            <div class="auth-error" data-error-for="password" @if (! $errors->has('password')) hidden @endif>@error('password')<i class="bi bi-exclamation-circle"></i> {{ $message }}@enderror</div>
            <div class="auth-caps" data-caps hidden><i class="bi bi-capslock-fill"></i> Caps Lock is on</div>
        </div>

        <div class="form-check auth-remember">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary auth-submit" data-submit>
            <span class="auth-submit-label">Sign in</span>
            <i class="bi bi-arrow-right auth-submit-icon"></i>
            <span class="spinner-border spinner-border-sm auth-submit-spinner" aria-hidden="true"></span>
        </button>
    </form>

    <p class="auth-alt" data-customer-only @if ($staff) hidden @endif>Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create a free account</a></p>

    <button type="button" class="auth-staff-link" data-staff-toggle>
        <span data-staff-off @if ($staff) hidden @endif><i class="bi bi-person-badge"></i> Admin / Staff? Sign in with email</span>
        <span data-staff-on @unless ($staff) hidden @endunless><i class="bi bi-phone"></i> Customer? Sign in with mobile number</span>
    </button>

    <div class="auth-secure"><i class="bi bi-shield-lock-fill"></i> Protected by 256-bit SSL encryption</div>

    @if (app()->environment('local'))
        <div class="auth-demo">
            <span>Demo:</span>
            <button type="button" data-demo="admin@bizsetu.test">Admin</button>
            <button type="button" data-demo="rahul.staff@bizsetu.test">Staff</button>
            <button type="button" data-demo="9876500001">Customer</button>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('[data-auth-form]');
        var login = document.getElementById('login');
        var password = document.getElementById('password');

        function setError(input, message) {
            var box = form.querySelector('[data-error-for="' + input.id + '"]');
            input.classList.toggle('is-invalid', !!message);
            box.hidden = !message;
            box.innerHTML = message ? '<i class="bi bi-exclamation-circle"></i> ' + message : '';
        }

        // Customer (mobile) <-> Admin / staff (email) switch on the same "login" field.
        var staff = login.type === 'email';
        function setStaff(on) {
            if (on === staff) return;
            staff = on;
            document.querySelector('[data-login-label]').textContent = on ? 'Work email' : 'Mobile number';
            document.querySelector('[data-login-icon]').className = 'bi ' + (on ? 'bi-envelope-fill' : 'bi-phone-fill');
            document.querySelector('[data-login-prefix]').hidden = on;
            document.querySelector('[data-login-wrap]').classList.toggle('has-prefix', !on);
            document.querySelector('[data-staff-off]').hidden = on;
            document.querySelector('[data-staff-on]').hidden = !on;
            document.querySelector('[data-customer-only]').hidden = on;
            login.type = on ? 'email' : 'tel';
            login.inputMode = on ? 'email' : 'numeric';
            login.maxLength = on ? 191 : 14;
            login.placeholder = on ? 'you@company.com' : '10-digit mobile number';
            login.dataset.requiredMsg = on ? 'Please enter your email address.' : 'Please enter your mobile number.';
            login.value = '';
            setError(login, '');
            login.focus();
        }
        document.querySelector('[data-staff-toggle]').addEventListener('click', function () { setStaff(!staff); });

        // Mobile mode: digits only (spaces allowed while typing)
        login.addEventListener('input', function () {
            if (!staff) login.value = login.value.replace(/[^\d ]/g, '');
        });

        // Friendly client-side check before posting; the server still validates everything.
        form.addEventListener('submit', function (e) {
            var firstBad = null;
            [login, password].forEach(function (input) {
                var bad = !input.value.trim();
                setError(input, bad ? input.dataset.requiredMsg : '');
                if (bad && !firstBad) firstBad = input;
            });
            if (!firstBad && !staff) {
                var digits = login.value.replace(/\D/g, '').slice(-10);
                if (!/^[6-9]\d{9}$/.test(digits)) {
                    setError(login, 'Enter a valid 10-digit mobile number.');
                    firstBad = login;
                }
            }
            if (firstBad) { e.preventDefault(); firstBad.focus(); return; }
            var btn = form.querySelector('[data-submit]');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        [login, password].forEach(function (input) {
            input.addEventListener('input', function () { if (input.value.trim()) setError(input, ''); });
        });

        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.togglePassword);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        });

        var caps = document.querySelector('[data-caps]');
        ['keydown', 'keyup'].forEach(function (evt) {
            password.addEventListener(evt, function (e) { if (e.getModifierState) caps.hidden = !e.getModifierState('CapsLock'); });
        });
        password.addEventListener('blur', function () { caps.hidden = true; });

        document.querySelectorAll('[data-demo]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setStaff(btn.dataset.demo.indexOf("@") > -1);
                login.value = btn.dataset.demo;
                password.value = 'Password@123';
                setError(login, ''); setError(password, '');
                password.focus();
            });
        });
    })();
</script>
@endpush
