@extends('layouts.site')

@section('title', (string) ('Pricing — Transparent Fees for Every Service'))
@section('meta_description', (string) ('Transparent professional fees for company registration, GST, trademark, licences, compliance and more. No hidden charges.'))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">Pricing</li></ol></nav>
        <h1>Simple, transparent pricing</h1>
        <p class="section-sub mb-0" style="max-width:720px">Professional fees shown below exclude GST. Government fees and stamp duty, where applicable, are charged at actuals — always shown before you pay.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach ($categories as $category)
                <a href="#p-{{ $category->slug }}" class="btn btn-sm btn-light"><i class="bi {{ $category->icon }} me-1"></i>{{ $category->name }}</a>
            @endforeach
        </div>

        <div class="row g-4">
            @foreach ($categories as $category)
                <div class="col-lg-6 pricing-cat" id="p-{{ $category->slug }}">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2"><span class="icon-bubble sm"><i class="bi {{ $category->icon }}"></i></span>{{ $category->name }}</div>
                        <div>
                            @foreach ($category->activeServices as $service)
                                <div class="pricing-row">
                                    <div class="name flex-grow-1 min-w-0">
                                        <a href="{{ route('site.services.show', $service->slug) }}" class="text-navy">{{ $service->name }}</a>
                                        <small>{{ $service->processing_time }}@if ($service->isRecurring()) · {{ $service->intervalLabel() }}@endif</small>
                                    </div>
                                    <div class="text-end">
                                        @if ($service->hasDiscount())<div class="price-old small">{{ money($service->price, false) }}</div>@endif
                                        <strong class="text-navy">{{ $service->effectivePrice() > 0 ? money($service->effectivePrice(), false) : 'Free' }}</strong>
                                    </div>
                                    <a href="{{ route('portal.applications.create', $service->slug) }}" class="btn btn-sm btn-cta">Apply</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<x-site.cta title="Need a custom package?" text="Bundle services and save — talk to us." />
@endsection
