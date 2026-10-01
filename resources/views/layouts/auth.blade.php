<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=57">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v=23">
</head>
<body class="auth-body">
@php($authImage = setting('hero_image') ? storage_asset(setting('hero_image')) : asset('images/site/hero.webp'))
<div class="auth-page">
    <div class="auth-frame">
        {{-- Left: business photo with brand + highlights (desktop only) --}}
        <aside class="auth-visual">
            <img src="{{ $authImage }}" alt="" class="auth-visual-img" fetchpriority="high">

            <div class="auth-visual-inner">
                @if (setting('logo'))
                    {{-- Uploaded logo is shown full width: it already carries the brand name --}}
                    <a href="{{ route('site.home') }}" class="auth-brand-logo" aria-label="{{ setting('company_name') }} home">
                        <img src="{{ storage_asset(setting('logo')) }}" alt="{{ setting('company_name') }}">
                    </a>
                @else
                    <a href="{{ route('site.home') }}" class="auth-brand">
                        <span class="auth-brand-mark"><i class="bi bi-bar-chart-steps"></i></span>
                        <span><strong>{{ setting('company_name') }}</strong><small>{{ setting('tagline') ?: 'Business Services Platform' }}</small></span>
                    </a>
                @endif

                <div class="auth-visual-bottom">
                    <h2>Your business compliance, <span>simplified.</span></h2>
                    <ul class="auth-points">
                        <li><i class="bi bi-check-circle-fill"></i> Track every application in real time</li>
                        <li><i class="bi bi-check-circle-fill"></i> All your documents in one secure place</li>
                        <li><i class="bi bi-check-circle-fill"></i> Reminders before every due date</li>
                    </ul>
                </div>
            </div>
        </aside>

        {{-- Right: the form --}}
        <main class="auth-main">
            <div class="auth-main-top">
                <span class="d-lg-none"><x-brand /></span>
                <a href="{{ route('site.home') }}" class="auth-back"><i class="bi bi-arrow-left"></i> Back to website</a>
            </div>
            <div class="auth-content">
                @yield('content')
            </div>
        </main>
    </div>

    <footer class="auth-footer">
        &copy; {{ date('Y') }} {{ setting('company_name') }}
        <a href="{{ route('site.privacy') }}">Privacy</a>
        <a href="{{ route('site.terms') }}">Terms</a>
        <a href="{{ route('site.contact') }}">Help</a>
    </footer>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
@stack('scripts')
</body>
</html>
