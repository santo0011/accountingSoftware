<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=1">
</head>
<body>
<div class="auth-wrap">
    <aside class="auth-side d-none d-lg-flex" style="width: 42%">
        <x-brand />
        <div>
            <h2 class="mb-3">Everything your business needs, in one place.</h2>
            <p class="mb-4">Registration, tax, compliance, legal and growth services — handled by experts and tracked online.</p>
            <ul class="list-unstyled d-grid gap-3 mb-0">
                <li class="d-flex gap-3"><i class="bi bi-shield-check fs-4 text-white"></i><span><strong class="text-white d-block">Secure document vault</strong>Your documents are private and encrypted.</span></li>
                <li class="d-flex gap-3"><i class="bi bi-activity fs-4 text-white"></i><span><strong class="text-white d-block">Real-time tracking</strong>Follow every step of your application.</span></li>
                <li class="d-flex gap-3"><i class="bi bi-bell fs-4 text-white"></i><span><strong class="text-white d-block">Never miss a due date</strong>Automatic compliance reminders.</span></li>
            </ul>
        </div>
        <div class="small">&copy; {{ date('Y') }} {{ setting('company_name') }}</div>
    </aside>
    <main class="auth-main">
        <div class="w-100 d-flex flex-column align-items-center">
            <div class="d-lg-none mb-4"><x-brand /></div>
            @yield('content')
            <a href="{{ route('site.home') }}" class="small text-muted mt-4"><i class="bi bi-arrow-left me-1"></i>Back to website</a>
        </div>
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</body>
</html>
