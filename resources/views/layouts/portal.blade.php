<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="{{ asset('assets/css/panel.css') }}?v=1">
    @stack('head')
</head>
<body class="panel">
@php
    $customer = auth()->user()->customer;
    $menu = [
        ['portal.dashboard', 'bi-grid-1x2', 'Dashboard', 'portal.dashboard'],
        ['portal.services.index', 'bi-briefcase', 'My Services', 'portal.services.*'],
        ['portal.applications.index', 'bi-folder2-open', 'Applications', 'portal.applications.*'],
        ['portal.documents.index', 'bi-file-earmark-text', 'Documents', 'portal.documents.*'],
        ['portal.payments.index', 'bi-credit-card', 'Payments', 'portal.payments.*'],
        ['portal.invoices.index', 'bi-receipt', 'Invoices', 'portal.invoices.*'],
        ['portal.compliance.index', 'bi-calendar-check', 'Compliance Calendar', 'portal.compliance.*'],
        ['portal.notifications.index', 'bi-bell', 'Notifications', 'portal.notifications.*'],
        ['portal.support.index', 'bi-headset', 'Support', 'portal.support.*'],
        ['portal.profile.edit', 'bi-person-circle', 'Profile', 'portal.profile.*'],
    ];
@endphp
<aside class="sidebar" aria-label="Customer navigation">
    <div class="sidebar-brand"><x-brand /></div>
    <nav class="sidebar-nav">
        <div class="nav-section">My Account</div>
        @foreach ($menu as [$route, $icon, $label, $pattern])
            <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}">
                <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                @if ($route === 'portal.notifications.index' && ($unreadCount ?? 0))<span class="badge bg-danger count">{{ $unreadCount }}</span>@endif
            </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}">@csrf
            <a href="#" onclick="event.preventDefault(); this.closest('form').submit();"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </form>
    </nav>
    <div class="sidebar-footer small">
        <div class="fw-semibold text-navy mb-1">Need help?</div>
        <div class="text-muted mb-2">Our experts are available {{ setting('business_hours') }}.</div>
        <a href="tel:{{ preg_replace('/\s+/', '', setting('company_phone')) }}" class="fw-semibold"><i class="bi bi-telephone me-1"></i>{{ setting('company_phone') }}</a>
    </div>
</aside>
<div class="sidebar-backdrop"></div>

<div class="panel-main">
    @include('layouts.partials.panel-topbar', ['area' => 'portal'])
    <main class="panel-content">
        <x-flash />
        @yield('content')
    </main>
    <footer class="panel-footer d-flex flex-wrap justify-content-between gap-2">
        <span>&copy; {{ date('Y') }} {{ setting('company_name') }} · Customer ID {{ $customer?->customer_code }}</span>
        <span><a href="{{ route('site.privacy') }}" class="text-muted">Privacy</a> · <a href="{{ route('site.terms') }}" class="text-muted">Terms</a></span>
    </footer>
</div>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/panel.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
