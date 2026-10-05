@extends('layouts.site')

@section('title', (string) ($q ? 'Search results for "'.$q.'"' : 'All Business Services'))
@section('meta_description', (string) ('Explore company registration, GST, trademark, licences, compliance, legal, HR and technology services with transparent pricing.'))
@if ($q)
    @section('robots', 'noindex, follow')
@endif

@php
    $total = $categories->sum(fn ($c) => $c->activeServices->count());
    $fromPrice = $categories->flatMap->activeServices->map->effectivePrice()->filter(fn ($p) => $p > 0)->min();
@endphp

@section('content')
<section class="cat-hero svc-hero">
    <div class="container position-relative">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Services</li>
        </ol></nav>
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h1>{{ $q ? 'Search results' : 'All business services' }}</h1>
                <p class="cat-hero-lead">Everything from registration to growth — pick a service to see documents, process, timeline and price.</p>
                <ul class="cat-hero-facts">
                    <li><i class="bi bi-grid"></i> {{ $total }} services</li>
                    <li><i class="bi bi-collection"></i> {{ $categories->count() }} categories</li>
                    @if ($fromPrice)<li><i class="bi bi-tag"></i> From {{ money($fromPrice, false) }}</li>@endif
                </ul>
            </div>
            <div class="col-lg-6">
                <form action="{{ route('site.services.index') }}" method="GET" class="hero-search hero-x-search svc-hero-search m-0" data-service-search="{{ route('site.services.search') }}" data-contact-url="{{ route('site.contact') }}" role="search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search e.g. GST, trademark, FSSAI" autocomplete="off" aria-label="Search services">
                    <button type="submit" class="btn btn-cta">Search</button>
                    <div class="search-results"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="svc-list-section" id="services-list">
    <div class="container">
      <div class="svc-layout">
        @if ($categories->isNotEmpty())
            {{-- Category filter: a sidebar on desktop, a swipeable bar on phones.
                 Plain anchors without JavaScript, instant filter with it. --}}
            <aside class="svc-side">
                <nav class="svc-nav" data-svc-filter aria-label="Service categories">
                    <div class="svc-nav-head">
                        <span>Categories</span>
                        <small>{{ $categories->count() }}</small>
                    </div>
                    <div class="svc-nav-list">
                        <a href="#services-list" class="svc-nav-item active" data-filter="all">
                            <span class="svc-nav-ico"><i class="bi bi-grid-fill"></i></span>
                            <span class="svc-nav-name">All services</span>
                            <span class="svc-nav-count">{{ $total }}</span>
                        </a>
                        @foreach ($categories as $category)
                            <a href="#cat-{{ $category->slug }}" class="svc-nav-item" data-filter="{{ $category->slug }}">
                                <span class="svc-nav-ico"><i class="bi {{ $category->icon }}"></i></span>
                                <span class="svc-nav-name">{{ $category->name }}</span>
                                <span class="svc-nav-count">{{ $category->activeServices->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </nav>
            </aside>
        @endif

        <div class="svc-main">
        @if ($q)
            <div class="svc-search-note">
                <span>{{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }} for <strong>“{{ $q }}”</strong></span>
                <a href="{{ route('site.services.index') }}"><i class="bi bi-x-circle"></i> Clear search</a>
            </div>
        @endif

        @forelse ($categories as $category)
            <div id="cat-{{ $category->slug }}" class="svc-group" data-svc-section="{{ $category->slug }}">
                <div class="svc-group-head">
                    <span class="svc-group-icon"><i class="bi {{ $category->icon }}"></i></span>
                    <div class="min-w-0">
                        <h2>{{ $category->name }} <span class="svc-group-count">{{ $category->activeServices->count() }}</span></h2>
                        @if ($category->tagline)<p>{{ $category->tagline }}</p>@endif
                    </div>
                    <a href="{{ route('site.categories.show', $category->slug) }}" class="link-arrow ms-auto">View category <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="svc-lite-grid">
                    @foreach ($category->activeServices as $service)
                        <x-site.service-lite :service="$service" />
                    @endforeach
                </div>
            </div>
        @empty
            <x-empty-state icon="bi-search" title="No services match your search" text="Try a different keyword or talk to our expert — we will guide you.">
                <a href="{{ route('site.contact') }}" class="btn btn-cta">Talk to an Expert</a>
            </x-empty-state>
        @endforelse
        </div>
      </div>
    </div>
</section>

<x-site.cta title="Not sure which service you need?" text="Our experts will guide you — free of charge." />
@endsection
