@extends('layouts.admin')
@section('title', (string) ($page->exists ? 'Edit Page' : 'New Page'))

@section('content')
<x-page-header :title="$page->exists ? 'Edit: '.$page->title : 'New page'" :back="route('admin.pages.index')" />

<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="card" novalidate>
    @csrf
    @if ($page->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="title" label="Title" :value="$page->title" required col="col-md-6 mb-3" />
        <x-form.input name="slug" label="Slug" :value="$page->slug" :readonly="in_array($page->slug, $system, true)" col="col-md-6 mb-3" />
        <x-form.textarea name="body" label="Content (HTML: p, h2, h3, ul, li, strong, a, table)" :value="$page->body" rows="16" class="font-monospace small" col="col-12 mb-3" />
        <x-form.input name="seo_title" label="SEO title" :value="$page->seo_title" col="col-md-6 mb-3" />
        <x-form.input name="seo_description" label="Meta description" :value="$page->seo_description" col="col-md-6 mb-3" />
        <x-form.check name="status" label="Published" :checked="$page->status" col="col-12" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Page</button></div>
</form>
@endsection
