@extends('layouts.admin')
@section('title', (string) ($page->exists ? 'Edit Page' : 'New Page'))

@section('content')
<x-page-header :title="$page->exists ? 'Edit: '.$page->title : 'New page'" :subtitle="$page->exists ? 'Update the page content shown on the website.' : 'Add a content page, e.g. Terms, Privacy or About.'" :back="route('admin.pages.index')" />

<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" novalidate>
    @csrf
    @if ($page->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Content" text="Allowed HTML: p, h2, h3, ul, li, strong, a, table." icon="bi-file-earmark-text">
                <x-form.input name="title" label="Title" :value="$page->title" required col="col-md-6 mb-3" />
                <x-form.input name="slug" label="Slug" :value="$page->slug" :readonly="in_array($page->slug, $system, true)" :help="in_array($page->slug, $system, true) ? 'Built-in page — the slug cannot change.' : 'Lowercase words joined by dashes, e.g. shipping-policy.'" col="col-md-6 mb-3" />
                <x-form.textarea name="body" label="Content (HTML)" :value="$page->body" rows="18" class="font-monospace small" col="col-12" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.section title="Publishing" text="Whether the page is live." icon="bi-broadcast" tone="teal">
                <x-form.check name="status" label="Published" :checked="$page->status" col="col-12" />
            </x-form.section>
            <x-form.section title="Search engines (SEO)" text="Optional — how the page appears in Google." icon="bi-search" tone="violet">
                <x-form.input name="seo_title" label="SEO title" :value="$page->seo_title" col="col-12 mb-3" />
                <x-form.textarea name="seo_description" label="Meta description" :value="$page->seo_description" rows="3" help="About 150 characters." col="col-12" />
            </x-form.section>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.pages.index')" :label="$page->exists ? 'Save changes' : 'Create page'" />
</form>
@endsection
