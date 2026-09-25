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
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('site.categories.show', $service->category->slug) }}">{{ $service->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $service->name }}</li>
        </ol></nav>
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-7">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge badge-soft-primary"><i class="bi {{ $service->category->icon }} me-1"></i>{{ $service->category->name }}</span>
                    @if ($service->isRecurring())<span class="badge badge-soft-teal"><i class="bi bi-arrow-repeat me-1"></i>{{ $service->intervalLabel() }} service</span>@endif
                    @if ($service->processing_time)<span class="badge badge-soft-secondary"><i class="bi bi-clock me-1"></i>{{ $service->processing_time }}</span>@endif
                </div>
                <h1>{{ $service->name }}</h1>
                <p class="section-sub">{{ $service->short_description }}</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ $applyUrl }}" class="btn btn-cta btn-lg">Apply Now <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="#pricing" class="btn btn-outline-primary btn-lg">View Pricing</a>
                </div>
                <ul class="hero-points">
                    <li><i class="bi bi-check-circle-fill"></i>Expert assisted</li>
                    <li><i class="bi bi-check-circle-fill"></i>100% online</li>
                    <li><i class="bi bi-check-circle-fill"></i>Real-time tracking</li>
                </ul>
            </div>
            <div class="col-lg-5 d-none d-md-block">
                <div class="page-hero-photo"><img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" width="800" height="534"></div>
            </div>
        </div>
    </div>
</section>

<nav class="service-nav" aria-label="Page sections">
    <div class="container">
        <div class="nav flex-nowrap overflow-auto">
            <a class="nav-link" href="#overview">Overview</a>
            @if ($service->who_needs)<a class="nav-link" href="#who">Who needs it</a>@endif
            @if ($service->benefits)<a class="nav-link" href="#benefits">Benefits</a>@endif
            @if ($service->documents->isNotEmpty())<a class="nav-link" href="#documents">Documents</a>@endif
            @if ($service->steps->isNotEmpty())<a class="nav-link" href="#process">Process</a>@endif
            <a class="nav-link" href="#pricing">Pricing</a>
            @if ($service->faqs->isNotEmpty())<a class="nav-link" href="#faq">FAQ</a>@endif
        </div>
    </div>
</nav>

<section>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="content-block prose" id="overview">
                    <h2>{{ $service->name }} — overview</h2>
                    {!! $service->full_description !!}
                </div>

                @if ($service->who_needs)
                    <div class="content-block" id="who">
                        <h2>Who needs this service?</h2>
                        <ul class="check-list row">
                            @foreach ($service->who_needs as $item)<li class="col-md-6">{{ $item }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                @if ($service->benefits)
                    <div class="content-block" id="benefits">
                        <h2>Benefits</h2>
                        <div class="row g-3">
                            @foreach ($service->benefits as $benefit)
                                <div class="col-md-6">
                                    <div class="feature-box align-items-center py-3">
                                        <span class="icon-bubble sm green"><i class="bi bi-check2-circle"></i></span>
                                        <div class="fw-semibold text-navy small">{{ $benefit }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($service->documents->isNotEmpty())
                    <div class="content-block" id="documents">
                        <h2>Documents required</h2>
                        <div class="row g-2">
                            @foreach ($service->documents as $doc)
                                <div class="col-md-6">
                                    <div class="doc-item"><i class="bi bi-file-earmark-text"></i>
                                        <span>{{ $doc->name }} @unless ($doc->is_mandatory)<small class="text-muted">(if applicable)</small>@endunless</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Accepted formats: PDF, JPG, PNG, DOC (max 5 MB each). You can also upload documents after applying.</p>
                    </div>
                @endif

                @if ($service->steps->isNotEmpty())
                    <div class="content-block" id="process">
                        <h2>Process &amp; timeline</h2>
                        @foreach ($service->steps as $step)
                            <div class="process-step">
                                <span class="num">{{ $loop->iteration }}</span>
                                <div>
                                    <h3>{{ $step->title }} @if ($step->duration)<span class="badge badge-soft-secondary ms-1 fw-semibold">{{ $step->duration }}</span>@endif</h3>
                                    <p>{{ $step->description }}</p>
                                </div>
                            </div>
                        @endforeach
                        @if ($service->processing_time)
                            <div class="alert alert-info mb-0 mt-2"><i class="bi bi-clock me-2"></i>Estimated timeline: <strong>{{ $service->processing_time }}</strong>, subject to government processing.</div>
                        @endif
                    </div>
                @endif

                <div class="content-block" id="pricing">
                    <h2>Pricing</h2>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <div class="fw-semibold text-navy">{{ $service->name }}</div>
                                    <div class="small text-muted">Professional fee{{ $service->isRecurring() ? ' · billed '.strtolower($service->intervalLabel()) : '' }}</div>
                                </div>
                                <div class="text-end">
                                    @if ($service->hasDiscount())<div class="price-old">{{ money($service->price) }}</div>@endif
                                    <div class="h3 mb-0">{{ money($price) }}</div>
                                    <div class="small text-muted">+ GST {{ rtrim(rtrim(number_format($service->gst_rate, 2), '0'), '.') }}% ({{ money($quote['tax']) }})</div>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <ul class="check-list small mb-3">
                                <li>Dedicated relationship manager &amp; expert professional</li>
                                <li>Document preparation, review and filing</li>
                                <li>Real-time tracking in your customer portal</li>
                                <li>GST invoice for your records</li>
                            </ul>
                            <p class="small text-muted mb-3">Government fees, stamp duty and other statutory charges (if any) are payable at actuals.</p>
                            <a href="{{ $applyUrl }}" class="btn btn-cta">Apply Now for {{ money($quote['total'], false) }} (incl. GST)</a>
                        </div>
                    </div>
                </div>

                @if ($service->faqs->isNotEmpty())
                    <div class="content-block" id="faq">
                        <h2>Frequently asked questions</h2>
                        <div class="accordion" id="serviceFaq">
                            @foreach ($service->faqs as $faq)
                                <div class="accordion-item">
                                    <h3 class="accordion-header"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sf{{ $faq->id }}">{{ $faq->question }}</button></h3>
                                    <div id="sf{{ $faq->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#serviceFaq"><div class="accordion-body text-muted">{{ $faq->answer }}</div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="col-lg-4 d-none d-lg-block">
                <div class="price-card p-4 mt-4">
                    <div class="small text-muted mb-1">Starting at</div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <span class="amount">{{ money($price, false) }}</span>
                        @if ($service->hasDiscount())<span class="price-old">{{ money($service->price, false) }}</span><span class="badge badge-soft-success">Save {{ $service->discountPercent() }}%</span>@endif
                    </div>
                    <div class="small text-muted mb-3">+ GST · {{ $service->isRecurring() ? 'per '.str_replace('ly', '', strtolower($service->intervalLabel())) : 'one-time fee' }}</div>
                    <a href="{{ $applyUrl }}" class="btn btn-cta w-100 btn-lg mb-2">Apply Now</a>
                    <a href="{{ route('site.contact', ['service' => $service->id]) }}" class="btn btn-outline-primary w-100">Talk to an Expert</a>
                    <div class="divider"></div>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        @if ($service->processing_time)<li><i class="bi bi-clock text-brand me-2"></i>{{ $service->processing_time }}</li>@endif
                        <li><i class="bi bi-file-earmark-text text-brand me-2"></i>{{ $service->documents->count() }} documents required</li>
                        <li><i class="bi bi-shield-check text-brand me-2"></i>Secure &amp; confidential</li>
                        <li><i class="bi bi-telephone text-brand me-2"></i>{{ setting('company_phone') }}</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
<section class="section-sm bg-soft mt-4">
    <div class="container">
        <h2 class="h4 mb-4">Related services</h2>
        <div class="row g-4">
            @foreach ($related as $item)
                <div class="col-sm-6 col-lg-3"><x-site.service-card :service="$item" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

<x-site.cta :title="'Get your '.$service->name.' done'" text="Apply online in minutes — our experts handle the rest." />

<div class="mobile-apply-bar d-lg-none d-flex align-items-center justify-content-between gap-3">
    <div>
        <div class="small text-muted lh-1">Starting at</div>
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
