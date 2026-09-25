@extends('layouts.site')

@section('title', (string) ($page->seo_title ?: $page->title))
@section('meta_description', (string) ($page->seo_description))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">{{ $page->title }}</li></ol></nav>
        <h1>{{ $page->title }}</h1>
        <p class="small text-muted mb-0">Last updated {{ $page->updated_at->format('d M Y') }}</p>
    </div>
</section>
<section class="section-sm">
    <div class="container">
        <div class="row"><div class="col-lg-9 prose">{!! $page->body !!}</div></div>
    </div>
</section>
@endsection
