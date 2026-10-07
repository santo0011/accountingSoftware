@extends('layouts.admin')
@section('title', (string) ($category->exists ? 'Edit Category' : 'Add Category'))

@section('content')
<x-page-header :title="$category->exists ? 'Edit '.$category->name : 'Add category'" :subtitle="$category->exists ? 'Update how this category appears on the website.' : 'Group related services under one heading on the website.'" :back="route('admin.categories.index')" />

<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($category->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Category details" text="Name, short line and description shown on the website." icon="bi-grid">
                <x-form.input name="name" label="Name" :value="$category->name" required placeholder="e.g. GST & Tax" col="col-md-6 mb-3" />
                <x-form.input name="slug" label="Slug" :value="$category->slug" help="Leave blank to generate from the name." col="col-md-6 mb-3" />
                <x-form.input name="tagline" label="Tagline" :value="$category->tagline" placeholder="One short line, e.g. GST and tax filings made simple" col="col-12 mb-3" />
                <x-form.textarea name="description" label="Description" :value="$category->description" rows="3" col="col-12 mb-3" />
                <x-form.input name="icon" label="Icon" :value="$category->icon" placeholder="bi-building" help="Any Bootstrap Icons class (icons.getbootstrap.com)." col="col-md-8 mb-3" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$category->sort_order" col="col-md-4 mb-3" />
                <x-form.check name="status" label="Visible on website" :checked="$category->status" col="col-12" />
            </x-form.section>

            <x-form.section title="Search engines (SEO)" text="Optional — how the category page appears in Google." icon="bi-search" tone="violet">
                <x-form.input name="seo_title" label="SEO title" :value="$category->seo_title" col="col-md-6 mb-md-0 mb-3" />
                <x-form.input name="seo_description" label="SEO description" :value="$category->seo_description" col="col-md-6" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.section title="Banner image" text="Shown at the top of the category page." icon="bi-image" tone="teal">
                <x-form.image name="banner" :current="$category->banner_image ? storage_asset($category->banner_image) : null"
                    ratio="16 / 7" help="JPG, PNG, WebP, GIF or JFIF, up to 2 MB. Wide images (about 1600×700 px) look best." col="col-12" />
            </x-form.section>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.categories.index')" :label="$category->exists ? 'Save changes' : 'Create category'" />
</form>
@endsection
