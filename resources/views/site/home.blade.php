@extends('layouts.site')

@section('title', (string) setting('seo_title'))
@section('meta_description', (string) setting('seo_description'))

@php($heroImage = setting('hero_image') ? storage_asset(setting('hero_image')) : asset('images/site/hero.webp').'?v=2')

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
@endpush

@section('content')

{{-- ============ HERO ============ --}}
<section class="hero-x">
    <div class="hero-x-glow" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 hero-x-copy">
                <span class="hero-x-badge"><span class="dot"></span> Trusted by {{ setting('stat_customers', '10,000+') }} businesses across India</span>
                <h1>Start, manage &amp; grow your business, <span class="hero-x-hl">all online.</span></h1>
                <p class="hero-x-lead">Company registration, GST, trademark and compliance — handled end-to-end by qualified CAs, CSs and lawyers, at fixed prices.</p>

                <form action="{{ route('site.services.index') }}" method="GET" class="hero-search hero-x-search" data-service-search="{{ route('site.services.search') }}" data-contact-url="{{ route('site.contact') }}" role="search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" class="form-control" placeholder="Try &quot;GST registration&quot;" autocomplete="off" aria-label="Search services">
                    <button type="submit" class="btn btn-cta">Search</button>
                    <div class="search-results"></div>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="hero-x-cats">
                        @foreach ($categories->take(4) as $item)
                            <a href="{{ route('site.categories.show', $item['category']->slug) }}" class="hero-x-cat">
                                <i class="bi {{ $item['category']->icon }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <ul class="hero-x-points">
                    <li><i class="bi bi-check-circle-fill"></i> 100% online</li>
                    <li><i class="bi bi-check-circle-fill"></i> Fixed pricing</li>
                    <li><i class="bi bi-check-circle-fill"></i> Expert-handled</li>
                </ul>
            </div>

            <div class="col-lg-6">
                <div class="hero-x-visual" data-tilt>
                    <div class="hero-x-photo">
                        <img src="{{ $heroImage }}" alt="Business team celebrating a completed registration" width="1100" height="900" fetchpriority="high">
                    </div>

                    {{-- Mock application tracker: shows what customers get after applying --}}
                    <div class="hero-x-tracker" aria-hidden="true">
                        <div class="trk-head">
                            <span class="trk-icon"><i class="bi bi-building"></i></span>
                            <span class="trk-title"><strong>Company Registration</strong><small>Application in progress</small></span>
                            <span class="trk-pct">75%</span>
                        </div>
                        <div class="trk-bar"><span></span></div>
                        <ul class="trk-steps">
                            <li class="done"><i class="bi bi-check-lg"></i> Documents verified</li>
                            <li class="done"><i class="bi bi-check-lg"></i> Name approved</li>
                            <li class="now"><i class="bi bi-arrow-repeat"></i> Filing incorporation</li>
                        </ul>
                    </div>

                    <div class="hero-x-rating" aria-hidden="true">
                        <div class="stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i></div>
                        <strong>{{ setting('stat_rating', '4.8/5') }}</strong>
                        <small>Customer rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="hero-x-stats">
    <div class="container">
        <div class="stats-band">
            <div class="row g-0">
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_customers', '10,000+') }}</div><div class="lbl">Happy customers</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_services', '70+') }}</div><div class="lbl">Business services</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_experts', '100+') }}</div><div class="lbl">CAs, CSs &amp; lawyers</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_rating', '4.8/5') }}</div><div class="lbl">Average rating</div></div>
            </div>
        </div>
    </div>
</div>

{{-- ============ POPULAR SERVICES ============ --}}
@if ($popular->isNotEmpty())
<section class="section section-tint" id="popular-services">
    <div class="container">
        <div class="section-bar">
            <x-site.section-head align="start" eyebrow="Most popular" title="Popular Services" />
            <a href="{{ route('site.services.index') }}" class="link-arrow">All services <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach ($popular as $service)
                <div class="col-sm-6 col-lg-3" data-reveal style="--d: {{ $loop->index * 80 }}ms"><x-site.service-card :service="$service" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ MAIN CATEGORIES ============ --}}
<section class="section section-plain" id="services">
    <div class="container">
        <x-site.section-head eyebrow="What we do" title="Everything Your Business Needs" sub="Choose a category to see all services." />
        <div class="row g-4">
            @foreach ($categories as $item)
                <div class="col-sm-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * 80 }}ms"><x-site.category-card :category="$item['category']" :label="$item['label']" :count="$item['category']->active_services_count" /></div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ BUSINESS TECHNOLOGY ============ --}}
@if ($technology->isNotEmpty())
<section class="section section-dark">
    <div class="container">
        <div class="section-bar">
            <x-site.section-head align="start" eyebrow="Business technology" title="Software That Grows Your Business" />
            <a href="{{ route('site.categories.show', 'business-technology') }}" class="link-arrow">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach ($technology as $service)
                <div class="col-sm-6 col-lg-4" data-reveal style="--d: {{ ($loop->index % 3) * 80 }}ms"><x-site.service-card :service="$service" cta="View Details" :price="false" :tag="false" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
<section class="section section-soft">
    <div class="container">
        <x-site.section-head eyebrow="How it works" title="Done in 4 Simple Steps" sub="No office visits, no paperwork chase. Track every step from your dashboard." />
        <ol class="steps-v3">
            @foreach ([
                ['bi-hand-index-thumb', 'Select a service', 'Pick what you need and see the price upfront.'],
                ['bi-cloud-arrow-up', 'Apply online', 'Fill a short form and upload your documents.'],
                ['bi-gear', 'Experts process it', 'A CA, CS or lawyer files it and keeps you updated.'],
                ['bi-file-earmark-check', 'Get your documents', 'Download your certificates from your account.'],
            ] as $i => [$icon, $title, $text])
                <li class="step-v3" data-reveal style="--d: {{ $i * 90 }}ms">
                    <span class="step-v3-no">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="step-v3-icon"><i class="bi {{ $icon }}"></i></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ============ WHY CHOOSE US ============ --}}
<section class="section section-plain">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5" data-reveal>
                <x-site.section-head align="start" eyebrow="Why choose us" title="Built on Trust & Expertise" sub="Every application is handled by a qualified professional, not a call centre. You always know what is happening and what it costs." />
                <ul class="why-checks">
                    <li><i class="bi bi-check2"></i> Fixed prices, no hidden charges</li>
                    <li><i class="bi bi-check2"></i> Live status tracking &amp; reminders</li>
                    <li><i class="bi bi-check2"></i> Support on call, email &amp; WhatsApp</li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-lg">Get Started <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="{{ route('site.contact') }}" class="btn btn-outline-primary btn-lg">Talk to an Expert</a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="why-grid-v3">
                    @foreach ([
                        ['bi-laptop', 'Easy Online Process', 'Apply from anywhere, on any device. No office visits.'],
                        ['bi-mortarboard', 'Professional Support', 'Qualified CAs, CSs and lawyers handle your work.'],
                        ['bi-eye', 'Transparent Process', 'Clear pricing upfront and live application tracking.'],
                        ['bi-shield-lock', 'Secure Documents', 'Your files are stored privately and protected.'],
                        ['bi-person-heart', 'Dedicated Assistance', 'One relationship manager for your business.'],
                        ['bi-headset', 'Always Reachable', 'Talk to us on call, email or WhatsApp.'],
                    ] as $i => [$icon, $title, $text])
                        <div class="why-v3" data-reveal style="--d: {{ $i * 70 }}ms">
                            <span class="why-icon"><i class="bi {{ $icon }}"></i></span>
                            <div>
                                <h3>{{ $title }}</h3>
                                <p>{{ $text }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ TESTIMONIALS ============ --}}
@if ($testimonials->isNotEmpty())
<section class="section section-soft">
    <div class="container">
        <x-site.section-head eyebrow="Customer stories" title="Loved by Business Owners" sub="Real feedback from founders and teams we have helped." />
        <div class="row g-4">
            @foreach ($testimonials->take(3) as $t)
                <div class="col-md-6 col-lg-4" data-reveal style="--d: {{ $loop->index * 90 }}ms">
                    <figure class="quote-card">
                        <i class="bi bi-quote quote-mark" aria-hidden="true"></i>
                        <div class="quote-stars" aria-label="{{ $t->rating }} out of 5 stars">{!! str_repeat('<i class="bi bi-star-fill"></i>', (int) $t->rating) !!}</div>
                        <blockquote>{{ $t->message }}</blockquote>
                        <figcaption>
                            <span class="quote-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::of($t->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                            <span>
                                <strong>{{ $t->name }}</strong>
                                <small>{{ collect([$t->designation, $t->company])->filter()->implode(', ') }}@if ($t->city) · {{ $t->city }}@endif</small>
                            </span>
                        </figcaption>
                    </figure>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ CTA ============ --}}
<section class="section-cta" data-reveal>
    <div class="container">
        <div class="cta-photo" style="--cta-img: url('{{ asset('images/site/cta.webp') }}')">
            <div class="cta-photo-inner">
                <h2>Need Help With Your Business?</h2>
                <p>Get professional support from registration to business growth.</p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-lg">Get Started <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="tel:{{ preg_replace('/\s+/', '', (string) setting('company_phone')) }}" class="btn btn-light btn-lg"><i class="bi bi-telephone me-1"></i> {{ setting('company_phone') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ FAQ ============ --}}
@if ($faqs->isNotEmpty())
<section class="section section-soft">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <x-site.section-head align="start" eyebrow="FAQ" title="Common Questions" sub="Quick answers about how we work. Can't find yours? Ask us directly." />
                <div class="faq-help">
                    <span class="why-icon"><i class="bi bi-chat-dots"></i></span>
                    <div>
                        <strong>Still have questions?</strong>
                        <small>Our experts reply within one working day.</small>
                        <a href="{{ route('site.contact') }}" class="link-arrow mt-2">Contact us <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="accordion faq-v2" id="homeFaq">
                    @foreach ($faqs as $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#hf{{ $faq->id }}">{{ $faq->question }}</button>
                            </h3>
                            <div id="hf{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#homeFaq">
                                <div class="accordion-body">{{ \Illuminate\Support\Str::limit($faq->answer, 160) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('site.faq') }}" class="link-arrow mt-3">View all FAQs <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
@endif
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values(),
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endpush
