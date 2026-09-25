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
    // [route, icon, label, active pattern, permission]
    $sections = [
        'Overview' => [
            ['admin.dashboard', 'bi-speedometer2', 'Dashboard', 'admin.dashboard', null],
            ['admin.reports.index', 'bi-bar-chart-line', 'Reports', 'admin.reports.*', 'reports.view'],
        ],
        'CRM' => [
            ['admin.customers.index', 'bi-people', 'Customers', 'admin.customers.*', 'customers.view'],
            ['admin.leads.index', 'bi-person-lines-fill', 'Leads', 'admin.leads.*', 'leads.view'],
        ],
        'Operations' => [
            ['admin.applications.index', 'bi-folder2-open', 'Applications', 'admin.applications.*', 'applications.view'],
            ['admin.documents.index', 'bi-file-earmark-check', 'Documents', 'admin.documents.*', 'documents.view'],
            ['admin.tasks.index', 'bi-check2-square', 'Tasks', 'admin.tasks.*', 'tasks.view'],
            ['admin.compliance.index', 'bi-calendar-check', 'Compliance', 'admin.compliance*', 'compliance.view'],
            ['admin.support.index', 'bi-headset', 'Support', 'admin.support.*', 'support.view'],
        ],
        'Billing' => [
            ['admin.payments.index', 'bi-credit-card', 'Payments', 'admin.payments.*', 'payments.view'],
            ['admin.invoices.index', 'bi-receipt', 'Invoices', 'admin.invoices.*', 'invoices.view'],
        ],
        'Catalogue' => [
            ['admin.categories.index', 'bi-grid', 'Service Categories', 'admin.categories.*', 'categories.manage'],
            ['admin.services.index', 'bi-briefcase', 'Services', 'admin.services.*', 'services.view'],
        ],
        'Team' => [
            ['admin.staff.index', 'bi-person-badge', 'Staff', 'admin.staff.*', 'staff.view'],
            ['admin.professionals.index', 'bi-mortarboard', 'Professionals', 'admin.professionals.*', 'professionals.view'],
            ['admin.roles.index', 'bi-shield-lock', 'Roles & Permissions', 'admin.roles.*', 'roles.manage'],
        ],
        'System' => [
            ['admin.pages.index', 'bi-window-stack', 'Website Content', 'admin.pages.*|admin.faqs.*|admin.testimonials.*', 'cms.manage'],
            ['admin.notifications.index', 'bi-bell', 'Notifications', 'admin.notifications.*', null],
            ['admin.settings.edit', 'bi-gear', 'Settings', 'admin.settings.*', 'settings.manage'],
            ['admin.audit.index', 'bi-journal-text', 'Audit Log', 'admin.audit.*', 'audit.view'],
        ],
    ];
    $user = auth()->user();
@endphp
<aside class="sidebar dark" aria-label="Admin navigation">
    <div class="sidebar-brand"><x-brand :href="route('admin.dashboard')" /></div>
    <nav class="sidebar-nav">
        @foreach ($sections as $section => $items)
            @php($visible = collect($items)->filter(fn ($i) => ! $i[4] || $user->can($i[4])))
            @continue($visible->isEmpty())
            <div class="nav-section">{{ $section }}</div>
            @foreach ($visible as [$route, $icon, $label, $pattern])
                <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode('|', $pattern)) ? 'active' : '' }}" title="{{ $label }}">
                    <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                    @if ($route === 'admin.notifications.index' && ($unreadCount ?? 0))<span class="badge bg-danger count">{{ $unreadCount }}</span>@endif
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="sidebar-footer small">
        Signed in as <strong class="text-white">{{ $user->getRoleNames()->map(fn ($r) => config("rbac.roles.$r.label", $r))->first() }}</strong>
    </div>
</aside>
<div class="sidebar-backdrop"></div>

<div class="panel-main">
    @include('layouts.partials.panel-topbar', ['area' => 'admin'])
    <main class="panel-content">
        <x-flash />
        @yield('content')
    </main>
    <footer class="panel-footer d-flex justify-content-between">
        <span>&copy; {{ date('Y') }} {{ setting('company_name') }} Admin</span>
        <span>v1.0</span>
    </footer>
</div>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/panel.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
