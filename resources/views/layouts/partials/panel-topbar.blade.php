{{-- Top bar shared by portal and admin. Expects: $area ('portal'|'admin'), $unreadNotifications, $unreadCount --}}
@php($user = auth()->user())
<header class="topbar-panel">
    <button class="icon-btn" type="button" data-sidebar-toggle aria-label="Toggle menu"><i class="bi bi-list"></i></button>

    @if ($area === 'admin')
        <form class="search d-none d-md-block" action="{{ route('admin.search') }}" method="GET" role="search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" class="form-control" placeholder="Search application no., customer, mobile, invoice…" value="{{ request('q') }}" aria-label="Search">
        </form>
    @else
        <div class="d-none d-md-block text-muted small">Welcome back, <strong class="text-navy">{{ \Illuminate\Support\Str::before($user->name, ' ') }}</strong></div>
    @endif

    <div class="ms-auto d-flex align-items-center gap-2">
        @if ($area === 'portal')
            <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-sm d-none d-sm-inline-flex"><i class="bi bi-plus-lg me-1"></i>Apply for a Service</a>
        @else
            <a href="{{ route('site.home') }}" target="_blank" class="icon-btn d-none d-sm-inline-flex" data-bs-toggle="tooltip" data-bs-placement="bottom" title="View website"><i class="bi bi-box-arrow-up-right"></i></a>
        @endif

        <div class="dropdown">
            <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                @if ($unreadCount ?? 0)<span class="dot-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-menu">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong class="text-navy small">Notifications</strong>
                    @if ($unreadCount ?? 0)
                        <form method="POST" action="{{ route($area.'.notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm p-0 small" data-no-lock>Mark all read</button></form>
                    @endif
                </div>
                @forelse ($unreadNotifications ?? [] as $n)
                    <a class="notif-item" href="{{ route($area.'.notifications.open', $n->id) }}">
                        <span class="icon-bubble sm {{ in_array($n->data['color'] ?? '', ['success', 'danger', 'warning'], true) ? ['success' => 'green', 'danger' => 'red', 'warning' => 'amber'][$n->data['color']] : '' }}"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
                        <span class="min-w-0"><strong>{{ $n->data['title'] ?? 'Notification' }}</strong><span class="text-truncate-2">{{ $n->data['message'] ?? '' }}</span><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></span>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted small"><i class="bi bi-bell-slash d-block fs-3 mb-1"></i>You're all caught up.</div>
                @endforelse
                <a href="{{ route($area.'.notifications.index') }}" class="d-block text-center small fw-semibold py-2">View all notifications</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar sm">{{ $user->initials() }}</span>
                <span class="d-none d-md-inline">{{ \Illuminate\Support\Str::limit($user->name, 18) }}</span>
                <i class="bi bi-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 small">
                    <div class="fw-semibold text-navy">{{ $user->name }}</div>
                    <div class="text-muted">{{ $user->email }}</div>
                    @if ($area === 'admin')<div class="mt-1"><span class="badge badge-soft-primary">{{ $user->getRoleNames()->map(fn ($r) => config("rbac.roles.$r.label", $r))->implode(', ') }}</span></div>@endif
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route($area.'.profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                @if ($area === 'portal')
                    <li><a class="dropdown-item" href="{{ route('portal.support.index') }}"><i class="bi bi-headset me-2"></i>Support</a></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="dropdown-item text-danger" data-no-lock><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
