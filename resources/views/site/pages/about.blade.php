@extends('layouts.site')

@section('title', (string) ($page->seo_title ?: $page->title))
@section('meta_description', (string) ($page->seo_description))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">{{ $page->title }}</li></ol></nav>
        <h1>{{ $page->title }}</h1>
        <p class="section-sub mb-0" style="max-width:720px">{{ setting('tagline') }} — with {{ setting('company_name') }}, your trusted partner for registration, compliance and growth.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7 prose">{!! $page->body !!}</div>
            <div class="col-lg-5">
                <div class="stats-band mb-4">
                    <div class="row g-0">
                        <div class="col-6 stat-item"><div class="num">{{ setting('stat_customers') }}</div><div class="lbl">Customers</div></div>
                        <div class="col-6 stat-item"><div class="num">{{ setting('stat_experts') }}</div><div class="lbl">Professionals</div></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">What we do</div>
                    <div class="list-group list-group-flush">
                        @foreach ($categories as $category)
                            <a href="{{ route('site.categories.show', $category->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 small">
                                <i class="bi {{ $category->icon }} text-brand"></i> {{ $category->name }}
                                <span class="ms-auto text-muted">{{ $category->active_services_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($testimonials->isNotEmpty())
<section class="section-sm bg-soft">
    <div class="container">
        <x-site.section-head title="What our customers say" />
        <div class="row g-4">
            @foreach ($testimonials as $t)
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="stars">{!! str_repeat('<i class="bi bi-star-fill"></i>', $t->rating) !!}</div>
                        <blockquote>“{{ $t->message }}”</blockquote>
                        <div class="who"><strong>{{ $t->name }}</strong><small>{{ $t->company }}</small></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<x-site.cta />
@endsection
