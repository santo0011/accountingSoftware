{{-- Shared <head> contents. Views set: @section('title'), @section('meta_description'), optional @section('canonical'). --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $company = setting('company_name', config('app.name'));
    $pageTitle = trim($__env->yieldContent('title')) ?: setting('seo_title', $company);
    $fullTitle = str_contains($pageTitle, $company) ? $pageTitle : $pageTitle.' | '.$company;
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: setting('seo_description');
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDescription), 160) }}">
@if ($robots = trim($__env->yieldContent('robots')))
    <meta name="robots" content="{{ $robots }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $company }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($metaDescription), 200) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0b2a4a">
<link rel="icon" href="{{ setting('favicon') ? storage_asset(setting('favicon')) : asset('favicon.ico') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=1">
