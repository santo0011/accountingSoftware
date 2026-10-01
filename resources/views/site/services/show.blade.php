@extends('layouts.site')

@section('title', (string) ($service->metaTitle()))
@section('meta_description', (string) ($service->metaDescription()))
@section('body_class', 'has-apply-bar')

@php
    $price = $service->effectivePrice();
    $applyUrl = route('portal.applications.create', $service->slug);
    $quote = app(\App\Services\InvoiceService::class)->quote($service);
@endphp

@section('content')
@php
    $categoryUrl = route('site.categories.show', $service->category->slug);
@endphp

{{-- ============ HERO ============ --}}
<section class="cat-hero sd-hero">
    <div class="container position-relative">
        <div class="sd-topline">
            {{-- Goes back in history when the visitor came from this site, otherwise to the category page --}}
            <a href="{{ $categoryUrl }}" class="sd-back" data-back><i class="bi bi-arrow-left"></i> Back</a>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('site.services.index') }}">Services</a></li>
                <li class="breadcrumb-item"><a href="{{ $categoryUrl }}">{{ $service->category->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
            </ol></nav>
        </div>

        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <a href="{{ $categoryUrl }}" class="sd-cat"><i class="bi {{ $service->category->icon }}"></i> {{ $service->category->name }}</a>
                <h1>{{ $service->name }}</h1>
                <p class="cat-hero-lead">{{ $service->short_description }}</p>
                <ul class="cat-hero-facts">
                    @if ($service->processing_time)<li><i class="bi bi-clock"></i> {{ $service->processing_time }}</li>@endif
                    @if ($service->documents->isNotEmpty())<li><i class="bi bi-file-earmark-text"></i> {{ $service->documents->count() }} documents</li>@endif
                    @if ($service->isRecurring())<li><i class="bi bi-arrow-repeat"></i> {{ $service->intervalLabel() }} service</li>@endif
                    <li><i class="bi bi-laptop"></i> 100% online</li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ $applyUrl }}" class="btn btn-cta">Apply Now <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="{{ route('site.contact', ['service' => $service->id]) }}" class="btn btn-ghost-light"><i class="bi bi-headset me-1"></i> Talk to an Expert</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="sd-photo">
                    <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" width="800" height="534" fetchpriority="high">
                    @if ($price > 0)
                        <div class="sd-photo-price">
                            <small>Starting from</small>
                            <strong>{{ money($price, false) }}</strong>
                            @if ($service->hasDiscount())<span>{{ $service->discountPercent() }}% off</span>@endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ BODY: only what a customer needs to decide and apply ============ --}}
<section class="sd-body">
    <div class="container">
        <div class="sd-layout">
            <div class="sd-main">
                @if ($service->documents->isNotEmpty())
                    <div class="sd-card" id="documents">
                        <h2 class="sd-h"><span><i class="bi bi-folder2-open"></i></span> Documents required</h2>
                        <div class="sd-docs">
                            @foreach ($service->documents as $doc)
                                <div class="sd-doc">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>{{ $doc->name }}</span>
                                    @unless ($doc->is_mandatory)<em>If applicable</em>@endunless
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($service->steps->isNotEmpty())
                    <div class="sd-card" id="process">
                        <h2 class="sd-h"><span><i class="bi bi-signpost-split"></i></span> How it works</h2>
                        <ol class="sd-flow">
                            @foreach ($service->steps as $step)
                                <li>
                                    <span class="sd-step-no">{{ $loop->iteration }}</span>
                                    <strong>{{ $step->title }}</strong>
                                    @if ($step->duration)<small>{{ $step->duration }}</small>@endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if ($service->faqs->isNotEmpty())
                    <div class="sd-card" id="faq">
                        <h2 class="sd-h"><span><i class="bi bi-question-circle"></i></span> Common questions</h2>
                        <div class="accordion faq-v2" id="serviceFaq">
                            @foreach ($service->faqs->take(3) as $faq)
                                <div class="accordion-item">
                                    <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sf{{ $faq->id }}">{{ $faq->question }}</button></h3>
                                    <div id="sf{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#serviceFaq"><div class="accordion-body">{{ $faq->answer }}</div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Price & apply --}}
            <aside class="sd-side">
                <div class="sd-buy" id="pricing">
                    <small class="sd-buy-label">Starting from</small>
                    <div class="sd-buy-price">
                        <strong>{{ money($price, false) }}</strong>
                        @if ($service->hasDiscount())<s>{{ money($service->price, false) }}</s><span>{{ $service->discountPercent() }}% off</span>@endif
                    </div>
                    <small class="sd-buy-sub">+ GST {{ rtrim(rtrim(number_format($service->gst_rate, 2), '0'), '.') }}% · {{ $service->isRecurring() ? 'billed '.strtolower($service->intervalLabel()) : 'one-time fee' }}</small>
                    <ul class="sd-buy-incl">
                        <li><i class="bi bi-check-circle-fill"></i> Expert CA / CS handles it</li>
                        <li><i class="bi bi-check-circle-fill"></i> Filing &amp; follow-up included</li>
                        <li><i class="bi bi-check-circle-fill"></i> Live tracking &amp; GST invoice</li>
                    </ul>
                    <a href="{{ $applyUrl }}" class="btn btn-cta w-100">Apply Now · {{ money($quote['total'], false) }} <small class="fw-normal opacity-75">incl. GST</small></a>
                    <a href="{{ route('site.contact', ['service' => $service->id]) }}" class="btn btn-outline-primary w-100">Talk to an Expert</a>
                    <p class="sd-buy-fine">Government fees (if any) are extra, at actuals.</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<div class="mobile-apply-bar d-lg-none d-flex align-items-center justify-content-between gap-3">
    <div>
        <div class="svc-price-label lh-1">Starting from</div>
        <div class="fw-bold text-navy fs-5">{{ money($price, false) }} <small class="text-muted fw-normal fs-6">+ GST</small></div>
    </div>
    <a href="{{ $applyUrl }}" class="btn btn-cta">Apply Now</a>
</div>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $service->name,
    'description' => $service->short_description,
    'provider' => ['@type' => 'Organization', 'name' => setting('company_name'), 'url' => url('/')],
    'areaServed' => 'IN',
    'offers' => $price > 0 ? ['@type' => 'Offer', 'price' => $price, 'priceCurrency' => setting('currency', 'INR')] : null,
]), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@if ($service->faqs->isNotEmpty())
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $service->faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endif
@endpush
