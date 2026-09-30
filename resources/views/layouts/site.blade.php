<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=42">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => setting('company_name'),
            'url' => url('/'),
            'telephone' => setting('company_phone'),
            'email' => setting('company_email'),
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('company_address'), 'addressRegion' => setting('company_state'), 'addressCountry' => 'IN'],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
    @stack('head')
</head>
<body class="@yield('body_class')">
<a href="#main" class="visually-hidden-focusable position-absolute p-2 bg-white">Skip to content</a>

@include('site.partials.header')

<main id="main">
    @if (session('success') || session('error'))
        <div class="container pt-3"><x-flash :summary="false" /></div>
    @endif
    @yield('content')
</main>

@include('site.partials.footer')

@if ($wa = setting('company_whatsapp'))
    <a class="whatsapp-fab" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Hi, I need help with a business service.') }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
@endif

<button type="button" class="back-to-top" aria-label="Back to top" title="Back to top">
    <svg class="btt-ring" viewBox="0 0 48 48" aria-hidden="true">
        <circle class="btt-track" cx="24" cy="24" r="22" />
        <circle class="btt-progress" cx="24" cy="24" r="22" />
    </svg>
    <i class="bi bi-chevron-up"></i>
</button>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('assets/js/site.js') }}?v=16" defer></script>
@stack('scripts')
</body>
</html>
