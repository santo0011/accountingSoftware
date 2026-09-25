@extends('layouts.auth')
@section('title', (string) ('Verify your email'))

@section('content')
<div class="auth-card text-center">
    <span class="icon-bubble lg green mb-3"><i class="bi bi-envelope-check"></i></span>
    <h1 class="h3 mb-2">Verify your email</h1>
    <p class="text-muted">We've sent a verification link to <strong class="text-navy">{{ auth()->user()->email }}</strong>. Click the link in the email to activate your account.</p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">A new verification link has been sent.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link text-muted small">Log out</button>
    </form>
</div>
@endsection
