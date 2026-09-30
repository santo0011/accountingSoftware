@extends('layouts.site')

@section('title', (string) ($category->metaTitle()))
@section('meta_description', (string) ($category->seo_description ?: $category->description))

@section('content')
@php($fromPrice = $category->activeServices->map->effectivePrice()->filter(fn ($p) => $p > 0)->min())
<section class="cat-hero">
    <div class="container position-relative">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('site.services.index') }}">Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol></nav>
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <div class="cat-hero-title">
                    <span class="cat-hero-icon"><i class="bi {{ $category->icon }}"></i></span>
                    <h1>{{ $category->name }}</h1>
                </div>
                @if ($category->tagline)<p class="cat-hero-lead">{{ $category->tagline }}</p>@endif
                <ul class="cat-hero-facts">
                    <li><i class="bi bi-grid"></i> {{ $category->activeServices->count() }} services</li>
                    @if ($fromPrice)<li><i class="bi bi-tag"></i> From {{ money($fromPrice, false) }}</li>@endif
                    <li><i class="bi bi-laptop"></i> 100% online</li>
                    <li><i class="bi bi-person-check"></i> Expert-handled</li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="#services" class="btn btn-cta">View Services <i class="bi bi-arrow-down ms-1"></i></a>
                    <a href="{{ route('site.contact') }}" class="btn btn-ghost-light">Talk to an Expert</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="cat-hero-photo"><img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" width="800" height="534" fetchpriority="high"></div>
            </div>
        </div>
    </div>
</section>

<section class="section-sm" id="services" style="scroll-margin-top:90px">
    <div class="container">
        <div class="row g-4">
            @foreach ($category->activeServices as $service)
                <div class="col-sm-6 col-lg-4 col-xl-3"><x-site.service-card :service="$service" /></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-sm bg-soft">
    <div class="container">
        <h2 class="h4 mb-4">Explore other categories</h2>
        <div class="row g-3">
            @foreach ($others as $other)
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ route('site.categories.show', $other->slug) }}" class="category-card align-items-center">
                        <img class="mega-thumb" src="{{ $other->thumbUrl() }}" alt="" width="64" height="48" loading="lazy">
                        <span><h3 class="mb-0">{{ $other->name }}</h3><small class="text-muted">{{ $other->active_services_count }} services</small></span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<x-site.cta />
@endsection
