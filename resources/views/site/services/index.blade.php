@extends('layouts.site')

@section('title', (string) ($q ? 'Search results for "'.$q.'"' : 'All Business Services'))
@section('meta_description', (string) ('Explore company registration, GST, trademark, licences, compliance, legal, HR and technology services with transparent pricing.'))
@if ($q)
    @section('robots', 'noindex, follow')
@endif

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">Services</li></ol></nav>
        <div class="row align-items-end g-4">
            <div class="col-lg-7">
                <h1>All business services</h1>
                <p class="section-sub mb-0">Everything from registration to growth — choose a service to see documents, process, timeline and pricing.</p>
            </div>
            <div class="col-lg-5">
                <form action="{{ route('site.services.index') }}" method="GET" class="hero-search m-0" data-service-search="{{ route('site.services.search') }}" data-contact-url="{{ route('site.contact') }}">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="Search services…" autocomplete="off" aria-label="Search services">
                    <div class="search-results"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3 d-none d-lg-block">
                <div class="card position-sticky" style="top:96px">
                    <div class="card-header">Categories</div>
                    <div class="list-group list-group-flush small">
                        @foreach ($categories as $category)
                            <a href="#cat-{{ $category->slug }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                <i class="bi {{ $category->icon }} text-brand"></i>{{ $category->name }}
                                <span class="ms-auto badge badge-soft-secondary">{{ $category->activeServices->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
            <div class="col-lg-9">
                @if ($q)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">Showing results for <strong class="text-navy">“{{ $q }}”</strong></div>
                        <a href="{{ route('site.services.index') }}" class="small">Clear search</a>
                    </div>
                @endif
                @forelse ($categories as $category)
                    <div id="cat-{{ $category->slug }}" class="mb-5" style="scroll-margin-top:100px">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h4 mb-0 d-flex align-items-center gap-2"><span class="icon-bubble sm"><i class="bi {{ $category->icon }}"></i></span>{{ $category->name }}</h2>
                            <a href="{{ route('site.categories.show', $category->slug) }}" class="small fw-semibold">View category <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="row g-3">
                            @foreach ($category->activeServices as $service)
                                <div class="col-sm-6 col-xl-4"><x-site.service-card :service="$service" /></div>
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
