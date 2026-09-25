<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="{{ asset('assets/css/panel.css') }}?v=10">
    @stack('head')
</head>
<body class="panel">
@php
    $user = auth()->user();
    $customer = $user->customer;
    $sectionIcons = ["Overview" => "bi-speedometer", "Docs & Billing" => "bi-wallet2", "Account" => "bi-person-gear"];
    // section => [route, icon, label, active pattern]
    $sections = [
        "Overview" => [
            ["portal.dashboard", "bi-grid-1x2", "Dashboard", "portal.dashboard"],
            ["portal.services.index", "bi-briefcase", "My Services", "portal.services.*"],
            ["portal.applications.index", "bi-folder2-open", "Applications", "portal.applications.*"],
        ],
        "Docs & Billing" => [
            ["portal.documents.index", "bi-file-earmark-text", "Documents", "portal.documents.*"],
            ["portal.payments.index", "bi-credit-card", "Payments", "portal.payments.*"],
            ["portal.invoices.index", "bi-receipt", "Invoices", "portal.invoices.*"],
        ],
        "Account" => [
            ["portal.compliance.index", "bi-calendar-check", "Compliance Calendar", "portal.compliance.*"],
            ["portal.notifications.index", "bi-bell", "Notifications", "portal.notifications.*"],
            ["portal.support.index", "bi-headset", "Support", "portal.support.*"],
            ["portal.profile.edit", "bi-person-circle", "Profile", "portal.profile.*"],
        ],
    ];
@endphp
<aside class="sidebar dark theme-customer" aria-label="Customer navigation">
    <div class="sidebar-brand"><x-brand :href="route('portal.dashboard')" /><span class="sidebar-badge">My Account</span></div>
    <nav class="sidebar-nav" data-nav-groups>
        <div class="nav-groups-bar">
            <span>Menu</span>
            <button type="button" class="nav-groups-all" data-nav-toggle-all title="Collapse / show current section"><i class="bi bi-arrows-collapse"></i></button>
        </div>
        @foreach ($sections as $section => $items)
            @php
                $hasActive = collect($items)->contains(fn ($i) => request()->routeIs($i[3]));
                $key = \Illuminate\Support\Str::slug($section);
                $pinned = $section === 'Overview';
            @endphp
            <div class="nav-group {{ $pinned || $hasActive ? 'open' : '' }} {{ $pinned ? 'pinned' : '' }} {{ $hasActive ? 'has-active' : '' }}" data-nav-group="{{ $key }}">
                <button type="button" class="nav-group-toggle" aria-expanded="{{ $pinned || $hasActive ? 'true' : 'false' }}" aria-controls="ng-{{ $key }}" @if ($pinned) tabindex="-1" @endif>
                    <i class="bi {{ $sectionIcons[$section] ?? 'bi-folder' }} nav-group-icon"></i>
                    <span class="nav-group-label">{{ $section }}</span>
                    <span class="nav-group-count">{{ count($items) }}</span>
                    @unless ($pinned)<i class="bi bi-chevron-down nav-group-chevron"></i>@endunless
                </button>
                <div class="nav-group-body" id="ng-{{ $key }}">
                    <div class="nav-group-inner">
                        @foreach ($items as [$route, $icon, $label, $pattern])
                            <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}" title="{{ $label }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-trigger="manual">
                                <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                                @if ($route === 'portal.notifications.index' && ($unreadCount ?? 0))<span class="badge bg-danger count">{{ $unreadCount }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
        <a href="tel:{{ preg_replace('/\s+/', '', (string) setting('company_phone')) }}" class="sidebar-help">
            <span class="sidebar-help-icon"><i class="bi bi-headset"></i></span>
            <span><strong>Need help?</strong><small>{{ setting('company_phone') }}</small></span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <span class="avatar">{{ $user->initials() }}</span>
            <span class="sidebar-user-info">
                <span class="sidebar-user-name">{{ $user->name }}</span>
                <span class="sidebar-user-role">{{ $customer?->customer_code }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="sidebar-logout" title="Logout" aria-label="Logout" data-no-lock><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
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
<script src="{{ asset('assets/js/panel.js') }}?v=4"></script>
@stack('scripts')
</body>
</html>
