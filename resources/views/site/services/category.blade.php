@extends('layouts.site')

@section('title', (string) ($category->metaTitle()))
@section('meta_description', (string) ($category->seo_description ?: $category->description))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('site.services.index') }}">Services</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol></nav>
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <span class="eyebrow"><i class="bi {{ $category->icon }}"></i> {{ $category->activeServices->count() }} services</span>
                <h1>{{ $category->name }}</h1>
                <p class="section-sub mb-4">{{ $category->tagline }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#services" class="btn btn-cta btn-lg">View Services</a>
                    <a href="{{ route('site.contact') }}" class="btn btn-outline-primary btn-lg">Talk to an Expert</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-md-block">
                <div class="page-hero-photo"><img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" width="800" height="534"></div>
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
