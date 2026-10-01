<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="{{ asset('assets/css/panel.css') }}?v=17">
    @stack('head')
</head>
<body class="panel panel-admin">
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
    $sectionIcons = ['Overview' => 'bi-speedometer', 'CRM' => 'bi-people', 'Operations' => 'bi-kanban', 'Billing' => 'bi-wallet2', 'Catalogue' => 'bi-grid', 'Team' => 'bi-person-badge', 'System' => 'bi-sliders'];
@endphp
<aside class="sidebar dark" aria-label="Admin navigation">
    <div class="sidebar-brand"><x-brand :href="route('admin.dashboard')" /><span class="sidebar-badge">Admin</span></div>
    <nav class="sidebar-nav" data-nav-groups>
        <div class="nav-groups-bar">
            <span>Menu</span>
            <button type="button" class="nav-groups-all" data-nav-toggle-all title="Collapse / show current section"><i class="bi bi-arrows-collapse"></i></button>
        </div>
        @foreach ($sections as $section => $items)
            @php
                $visible = collect($items)->filter(fn ($i) => ! $i[4] || $user->can($i[4]));
                $hasActive = $visible->contains(fn ($i) => request()->routeIs(...explode('|', $i[3])));
                $key = \Illuminate\Support\Str::slug($section);
                $pinned = $section === 'Overview';
            @endphp
            @continue($visible->isEmpty())
            <div class="nav-group {{ $pinned || $hasActive ? 'open' : '' }} {{ $pinned ? 'pinned' : '' }} {{ $hasActive ? 'has-active' : '' }}" data-nav-group="{{ $key }}">
                <button type="button" class="nav-group-toggle" aria-expanded="{{ $pinned || $hasActive ? 'true' : 'false' }}" aria-controls="ng-{{ $key }}" @if ($pinned) tabindex="-1" @endif>
                    <i class="bi {{ $sectionIcons[$section] ?? 'bi-folder' }} nav-group-icon"></i>
                    <span class="nav-group-label">{{ $section }}</span>
                    <span class="nav-group-count">{{ $visible->count() }}</span>
                    @unless ($pinned)<i class="bi bi-chevron-down nav-group-chevron"></i>@endunless
                </button>
                <div class="nav-group-body" id="ng-{{ $key }}">
                    <div class="nav-group-inner">
                        @foreach ($visible as [$route, $icon, $label, $pattern])
                            <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode('|', $pattern)) ? 'active' : '' }}" title="{{ $label }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-trigger="manual">
                                <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                                @if ($route === 'admin.notifications.index' && ($unreadCount ?? 0))<span class="badge bg-danger count">{{ $unreadCount }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <span class="avatar">{{ $user->initials() }}</span>
            <span class="sidebar-user-info">
                <span class="sidebar-user-name">{{ $user->name }}</span>
                <span class="sidebar-user-role">{{ $user->getRoleNames()->map(fn ($r) => config("rbac.roles.$r.label", $r))->first() }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}" data-logout>@csrf
                <button class="sidebar-logout" title="Logout" aria-label="Logout" data-no-lock><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
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

@include('layouts.partials.logout-modal')
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/panel.js') }}?v=6"></script>
@stack('scripts')
</body>
</html>
