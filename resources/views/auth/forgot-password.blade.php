@extends('layouts.auth')
@section('title', (string) ('Forgot password'))

@section('content')
<div class="auth-card">
    <span class="icon-bubble lg mb-3"><i class="bi bi-key"></i></span>
    <h1 class="h3 mb-1">Forgot your password?</h1>
    <p class="text-muted mb-4">Enter your registered email and we will send you a link to reset it.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-form.input name="email" label="Email address" type="email" required autofocus autocomplete="email" />
        <button type="submit" class="btn btn-primary w-100 btn-lg">Send Reset Link</button>
    </form>
    <div class="divider"></div>
    <p class="text-center small mb-0"><a href="{{ route('login') }}" class="fw-semibold"><i class="bi bi-arrow-left me-1"></i>Back to login</a></p>
</div>
@endsection
