@extends('layouts.site')

@section('title', (string) setting('seo_title'))
@section('meta_description', (string) setting('seo_description'))

@php($heroImage = setting('hero_image') ? storage_asset(setting('hero_image')) : asset('images/site/hero.webp').'?v=2')

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
@endpush

@section('content')

{{-- ============ HERO ============ --}}
<section class="hero-v2">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi bi-patch-check-fill"></i> One platform for all your business needs</span>
                <h1>Start, Manage &amp; Grow <span class="hl">Your Business</span></h1>
                <p class="hero-lead">Registration, Tax, Compliance &amp; Business Support — all in one place.</p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-lg">Get Started <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="#services" class="btn btn-outline-primary btn-lg">Explore Services</a>
                </div>
                <ul class="hero-points">
                    <li><i class="bi bi-check-circle-fill"></i>100% online</li>
                    <li><i class="bi bi-check-circle-fill"></i>Expert support</li>
                    <li><i class="bi bi-check-circle-fill"></i>Transparent pricing</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="hero-photo" data-tilt>
                    <div class="hero-photo-frame">
                        <img src="{{ $heroImage }}" alt="Indian business team reviewing documents together" width="1100" height="900" fetchpriority="high">
                    </div>
                    <div class="hero-chip chip-1">
                        <span class="icon-bubble sm green chip-check">
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M7.5 12.5l3 3 6-6.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span><strong>Registration complete</strong><small>Certificate delivered online</small></span>
                    </div>
                    <div class="hero-chip chip-2">
                        <span class="icon-bubble sm amber chip-star"><i class="bi bi-star-fill"></i></span>
                        <span><strong>{{ setting('stat_rating', '4.8/5') }}</strong><small>Customer rating</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-band mt-5">
            <div class="row g-0">
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_customers', '10,000+') }}</div><div class="lbl">Happy customers</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_services', '70+') }}</div><div class="lbl">Business services</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_experts', '100+') }}</div><div class="lbl">CAs, CSs &amp; lawyers</div></div>
                <div class="col-6 col-md-3 stat-item"><div class="num">{{ setting('stat_rating', '4.8/5') }}</div><div class="lbl">Average rating</div></div>
            </div>
        </div>
    </div>
</section>

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
                <div class="col-sm-6 col-lg-3"><x-site.service-card :service="$service" /></div>
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
                <div class="col-sm-6 col-lg-4"><x-site.category-card :category="$item['category']" :label="$item['label']" :count="$item['category']->active_services_count" /></div>
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
                <div class="col-sm-6 col-lg-4"><x-site.service-card :service="$service" cta="View Details" :price="false" :tag="false" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
<section class="section section-soft">
    <div class="container">
        <x-site.section-head eyebrow="How it works" title="Done in 4 Simple Steps" />
        <div class="steps-v2">
            @foreach ([
                ['bi-hand-index-thumb', 'Select Service', 'Choose the service you need.'],
                ['bi-cloud-arrow-up', 'Apply Online', 'Submit your details and documents.'],
                ['bi-gear', 'We Process', 'Our team handles the process.'],
                ['bi-file-earmark-check', 'Get Your Documents', 'Receive your completed documents.'],
            ] as $i => [$icon, $title, $text])
                <div class="step-v2">
                    <div class="step-v2-icon"><i class="bi {{ $icon }}"></i><span class="step-v2-no">{{ $i + 1 }}</span></div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ WHY CHOOSE US ============ --}}
<section class="section section-plain">
    <div class="container">
        <x-site.section-head eyebrow="Why choose us" title="Built on Trust & Expertise" />
        <div class="why-grid">
            @foreach ([
                ['bi-laptop', 'Easy Online Process', 'Apply from anywhere.'],
                ['bi-mortarboard', 'Professional Support', 'Qualified CAs, CSs & lawyers.'],
                ['bi-eye', 'Transparent Process', 'Clear pricing, live tracking.'],
                ['bi-shield-lock', 'Secure Documents', 'Private, protected storage.'],
                ['bi-person-heart', 'Dedicated Assistance', 'One manager for your business.'],
            ] as [$icon, $title, $text])
                <div class="why-item">
                    <span class="why-icon"><i class="bi {{ $icon }}"></i></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="section-cta">
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
    <div class="container" style="max-width: 860px">
        <x-site.section-head eyebrow="FAQ" title="Common Questions" />
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
        <div class="text-center mt-4"><a href="{{ route('site.faq') }}" class="link-arrow">View all FAQs <i class="bi bi-arrow-right"></i></a></div>
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
