@extends('layouts.auth')
@section('title', (string) ('Login'))

@section('content')
<div class="auth-card">
    <h1 class="h3 mb-1">Welcome back</h1>
    <p class="text-muted mb-4">Log in to track your applications, documents and compliance.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <x-form.input name="login" label="Email or mobile number" required autofocus autocomplete="username" placeholder="you@example.com or 98XXXXXXXX" />

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label required">Password</label>
                <a href="{{ route('password.request') }}" class="small fw-semibold">Forgot password?</a>
            </div>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Keep me logged in</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">Login</button>
    </form>

    <div class="divider"></div>
    <p class="text-center mb-0 small">New to {{ setting('company_name') }}? <a href="{{ route('register') }}" class="fw-semibold">Create a free account</a></p>

    @if (app()->environment('local'))
        <div class="alert alert-info small mt-4 mb-0">
            <strong>Demo logins</strong> (password <code>Password@123</code>)<br>
            Admin: <code>admin@bizsetu.test</code><br>
            Staff: <code>rahul.staff@bizsetu.test</code><br>
            Customer: <code>customer@bizsetu.test</code> or <code>9876500001</code>
        </div>
    @endif
</div>
@endsection
