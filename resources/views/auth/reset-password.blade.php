@extends('layouts.auth')
@section('title', (string) ('Reset password'))

@section('content')
<div class="auth-card">
    <h1 class="h3 mb-1">Set a new password</h1>
    <p class="text-muted mb-4">Choose a strong password you haven't used before.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-form.input name="email" label="Email address" type="email" :value="$request->email" required autocomplete="email" />
        <x-form.input name="password" label="New password" type="password" required autocomplete="new-password" />
        <x-form.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
        <button type="submit" class="btn btn-primary w-100 btn-lg">Reset Password</button>
    </form>
</div>
@endsection
