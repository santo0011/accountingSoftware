<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v=5">
</head>
<body class="auth-body">
@php($authImage = setting('hero_image') ? storage_asset(setting('hero_image')) : asset('images/site/hero.webp'))
<div class="auth-page">
    <div class="auth-frame">
        {{-- Left: business photo with brand + highlights (desktop only) --}}
        <aside class="auth-visual">
            <img src="{{ $authImage }}" alt="" class="auth-visual-img" fetchpriority="high">
            <div class="auth-visual-inner">
                <a href="{{ route('site.home') }}" class="auth-brand">
                    <span class="auth-brand-mark">
                        @if (setting('logo'))<img src="{{ storage_asset(setting('logo')) }}" alt="">@else<i class="bi bi-bar-chart-steps"></i>@endif
                    </span>
                    <span><strong>{{ setting('company_name') }}</strong><small>{{ setting('tagline') ?: 'Business Services Platform' }}</small></span>
                </a>

                <div class="auth-visual-copy">
                    <span class="auth-chip"><i class="bi bi-shield-check"></i> Trusted by {{ setting('stat_customers', '25,000+') }} businesses</span>
                    <h2>Your business compliance, simplified.</h2>
                    <p>Registration, tax, compliance and legal services — handled by experts and tracked in one secure place.</p>
                </div>

                <ul class="auth-features">
                    <li><span class="auth-feature-icon"><i class="bi bi-lightning-charge"></i></span><span><strong>Real-time tracking</strong><small>Follow every step of every application</small></span></li>
                    <li><span class="auth-feature-icon"><i class="bi bi-lock"></i></span><span><strong>Secure document vault</strong><small>Private, encrypted storage for your files</small></span></li>
                    <li><span class="auth-feature-icon"><i class="bi bi-bell"></i></span><span><strong>Due-date reminders</strong><small>Never miss a filing or renewal again</small></span></li>
                </ul>

                <div class="auth-stats">
                    <div><strong>{{ setting('stat_customers', '25,000+') }}</strong><span>Customers</span></div>
                    <div><strong>{{ setting('stat_experts', '150+') }}</strong><span>Experts</span></div>
                    <div><strong>{{ setting('stat_rating', '4.8/5') }}</strong><span>Rating</span></div>
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
