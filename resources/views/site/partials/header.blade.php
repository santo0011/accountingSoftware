@php
    $bySlug = $menuCategories->keyBy('slug');
    // Top-level menu => [intro line, category slugs (first one leads), help card [icon, title, text]]
    $navGroups = [
        'Business Registration' => ['Company registration, licences and other registrations.', ['business-registration', 'licenses-certification', 'import-export', 'ngo-services'],
            ['bi-building-add', 'Starting a new business?', 'An expert will help you pick the right structure — Pvt Ltd, LLP, OPC or proprietorship.']],
        'Tax & Compliance' => ['GST, TDS, accounting and annual filings by CAs.', ['gst-tax', 'accounting-bookkeeping', 'annual-compliance'],
            ['bi-calendar2-check', 'Never miss a due date', 'Our CAs track your GST, TDS and ROC deadlines and file on time.']],
        'Trademark' => ['Protect your brand name and logo with IP attorneys.', ['trademark-ipr'],
            ['bi-shield-check', 'Protect your brand today', 'Our IP attorneys check your brand name before filing to avoid objections.']],
        'Business Growth' => ['Technology, legal support and expert advisory.', ['business-technology', 'business-consultancy', 'virtual-cxo', 'corporate-advisory', 'legal-odr'],
            ['bi-rocket-takeoff', 'Not sure where to start?', 'Tell us your goals — a growth expert will suggest the right mix of tech, advisory and legal support.']],
    ];
    $phone = setting('company_phone');
    $tel = preg_replace('/\s+/', '', (string) $phone);
@endphp
<div class="topbar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <div class="d-flex gap-4">
            <a href="tel:{{ $tel }}"><i class="bi bi-telephone me-1"></i>{{ $phone }}</a>
            <a href="mailto:{{ setting('company_email') }}"><i class="bi bi-envelope me-1"></i>{{ setting('company_email') }}</a>
            <span><i class="bi bi-clock me-1"></i>{{ setting('business_hours') }}</span>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('portal.applications.index') }}"><i class="bi bi-search me-1"></i>Track Application</a>
            <a href="{{ route('site.faq') }}">Help Center</a>
        </div>
    </div>
</div>

<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
        <div class="container">
            <x-brand class="navbar-brand me-4" />

            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    {{-- Services: every category as an icon tile --}}
                    <li class="nav-item dropdown has-mega">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('site.services.*', 'site.categories.*') ? 'active' : '' }}" href="{{ route('site.services.index') }}" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Services</a>
                        <div class="dropdown-menu mega-menu">
                            <div class="mega-grid">
                                <div class="mega-main">
                                    <div class="mega-head">
                                        <div>
                                            <div class="mega-title">All services</div>
                                            <div class="mega-sub">{{ $menuCategories->sum(fn ($c) => $c->activeServices->count()) }} services across {{ $menuCategories->count() }} categories</div>
                                        </div>
                                        <a href="{{ route('site.services.index') }}" class="mega-viewall">Browse all services <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                    <div class="growth-grid is-wide">
                                        <div class="growth-tiles cols-3">
                                            @foreach ($menuCategories as $cat)
                                                <a class="growth-tile" href="{{ route('site.categories.show', $cat->slug) }}">
                                                    <span class="growth-ico"><i class="bi {{ $cat->icon }}"></i></span>
                                                    <span class="min-w-0"><strong>{{ $cat->name }}</strong><small>{{ $cat->activeServices->count() }} services</small></span>
                                                </a>
                                            @endforeach
                                        </div>
                                        @include('site.partials.mega-promo', ['promo' => ['bi-compass', 'Need help choosing?', 'Tell us about your business and an expert will point you to the right services.']])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    {{-- Grouped mega menus: the lead category as icon tiles, the rest as rows with quick links, plus a help card --}}
                    @foreach ($navGroups as $label => [$intro, $slugs, $promo])
                        @php($cats = collect($slugs)->map(fn ($s) => $bySlug->get($s))->filter())
                        @continue($cats->isEmpty())
                        @php($lead = $cats->first())
                        @php($rest = $cats->slice(1))
                        @php($isActive = request()->routeIs('site.categories.show') && in_array(request()->route('category')?->slug, $slugs, true))
                        <li class="nav-item dropdown has-mega">
                            <a class="nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}" href="{{ route('site.categories.show', $lead->slug) }}" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">{{ $label }}</a>
                            <div class="dropdown-menu mega-menu">
                                <div class="mega-grid">
                                    <div class="mega-main">
                                        <div class="mega-head">
                                            <div>
                                                <div class="mega-title">{{ $label }}</div>
                                                <div class="mega-sub">{{ $intro }}</div>
                                            </div>
                                            <a href="{{ route('site.categories.show', $lead->slug) }}" class="mega-viewall">View all <i class="bi bi-arrow-right"></i></a>
                                        </div>

                                        <div class="growth-grid {{ $rest->isEmpty() ? 'is-wide' : '' }}">
                                            <div class="growth-main">
                                                <a href="{{ route('site.categories.show', $lead->slug) }}" class="growth-label">
                                                    <i class="bi {{ $lead->icon }}"></i> {{ $lead->name }}
                                                    <span class="growth-count">{{ $lead->activeServices->count() }}</span>
                                                </a>
                                                <div class="growth-tiles {{ $rest->isEmpty() ? 'cols-3' : '' }}">
                                                    @foreach ($lead->activeServices->take(8) as $svc)
                                                        <a class="growth-tile" href="{{ route('site.services.show', $svc->slug) }}">
                                                            <span class="growth-ico"><i class="bi {{ $svc->icon ?: $lead->icon }}"></i></span>
                                                            <span class="min-w-0">
                                                                <strong>{{ $svc->name }}</strong>
                                                                <small>@if ($svc->is_featured)<span class="mega-badge">Popular</span>@endif{{ $svc->short_description }}</small>
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                                @if ($lead->activeServices->count() > 8)
                                                    <a href="{{ route('site.categories.show', $lead->slug) }}" class="growth-more">+{{ $lead->activeServices->count() - 8 }} more {{ $lead->name }} services <i class="bi bi-arrow-right"></i></a>
                                                @endif
                                            </div>

                                            @if ($rest->isNotEmpty())
                                                <div class="growth-side">
                                                    <div class="growth-label static"><i class="bi bi-grid"></i> More in {{ $label }}</div>
                                                    @foreach ($rest as $cat)
                                                        @php($only = $cat->activeServices->count() === 1 ? $cat->activeServices->first() : null)
                                                        <div class="growth-row">
                                                            <span class="growth-ico sm"><i class="bi {{ $cat->icon }}"></i></span>
                                                            <span class="min-w-0 flex-grow-1">
                                                                <a class="growth-row-title stretched-link" href="{{ $only ? route('site.services.show', $only->slug) : route('site.categories.show', $cat->slug) }}">{{ $cat->name }}</a>
                                                                @if ($only)
                                                                    <small>{{ $cat->tagline }}</small>
                                                                @else
                                                                    <span class="growth-links">
                                                                        @foreach ($cat->activeServices->take(3) as $svc)
                                                                            <a href="{{ route('site.services.show', $svc->slug) }}">{{ \Illuminate\Support\Str::of($svc->name)->replace(' Registration', '') }}</a>
                                                                        @endforeach
                                                                        @if ($cat->activeServices->count() > 3)<span>+{{ $cat->activeServices->count() - 3 }}</span>@endif
                                                                    </span>
                                                                @endif
                                                            </span>
                                                            <i class="bi bi-arrow-right growth-go"></i>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @include('site.partials.mega-promo', ['promo' => $promo])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach

                </ul>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                @auth
                    @php($initials = \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode(''))
                    <a href="{{ route('dashboard') }}" class="hdr-account d-none d-sm-inline-flex" title="My Account">
                        <span class="hdr-avatar">{{ $initials }}</span>
                        <span class="hdr-account-label">My Account</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-link text-navy fw-semibold d-none d-sm-inline-flex">Login</a>
                @endauth
                <a href="{{ route('site.contact') }}" class="btn btn-cta d-none d-sm-inline-flex align-items-center text-nowrap"><i class="bi bi-chat-dots me-2"></i>Contact</a>
                <button class="btn btn-light btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>
        </div>
    </nav>
</header>

{{-- Mobile navigation --}}
<div class="offcanvas offcanvas-end offcanvas-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header border-bottom">
        <x-brand id="mobileNavLabel" />
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="accordion accordion-flush mb-2" id="mobileCats">
            @foreach ($navGroups as $label => [$intro, $slugs])
                @php($cats = collect($slugs)->map(fn ($s) => $bySlug->get($s))->filter())
                @continue($cats->isEmpty())
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mc{{ $loop->index }}">{{ $label }}</button>
                    </h2>
                    <div id="mc{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#mobileCats">
                        <div class="pb-3">
                            @foreach ($cats as $cat)
                                @if ($cats->count() > 1)
                                    <a href="{{ route('site.categories.show', $cat->slug) }}" class="mega-col-head mt-2"><span class="icon-bubble sm"><i class="bi {{ $cat->icon }}"></i></span>{{ $cat->name }}</a>
                                @endif
                                <ul class="mega-list mb-2">
                                    @foreach ($cat->activeServices->take($cats->count() > 1 ? 4 : 10) as $svc)
                                        <li><a href="{{ route('site.services.show', $svc->slug) }}">{{ $svc->name }}</a></li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <nav class="nav flex-column">
            <a class="nav-link text-navy fw-semibold" href="{{ route('site.services.index') }}">All Services</a>
            <a class="nav-link text-navy fw-semibold" href="{{ route('site.pricing') }}">Pricing</a>
            <a class="nav-link text-navy fw-semibold" href="{{ route('site.faq') }}">FAQ</a>
            <a class="nav-link text-navy fw-semibold" href="{{ route('site.about') }}">About Us</a>
        </nav>
        <div class="d-grid gap-2 mt-4">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">My Account</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
            @endauth
            <a href="{{ route('site.contact') }}" class="btn btn-cta"><i class="bi bi-chat-dots me-2"></i>Contact</a>
        </div>
        <div class="small text-muted mt-4">
            <div><i class="bi bi-telephone me-1"></i>{{ $phone }}</div>
            <div><i class="bi bi-envelope me-1"></i>{{ setting('company_email') }}</div>
        </div>
    </div>
</div>
