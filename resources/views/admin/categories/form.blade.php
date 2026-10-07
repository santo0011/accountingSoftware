@extends('layouts.admin')
@section('title', (string) ($category->exists ? 'Edit Category' : 'Add Category'))

@section('content')
<x-page-header :title="$category->exists ? 'Edit '.$category->name : 'Add category'" :back="route('admin.categories.index')" />

<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Name" :value="$category->name" required col="col-md-6 mb-3" />
        <x-form.input name="slug" label="Slug" :value="$category->slug" help="Leave blank to generate from the name." col="col-md-6 mb-3" />
        <x-form.input name="tagline" label="Tagline" :value="$category->tagline" col="col-12 mb-3" />
        <x-form.textarea name="description" label="Description" :value="$category->description" rows="3" col="col-12 mb-3" />
        <x-form.input name="icon" label="Icon" :value="$category->icon" placeholder="bi-building" help="Any Bootstrap Icons class (icons.getbootstrap.com)." col="col-md-4 mb-3" />
        <x-form.input name="sort_order" type="number" label="Sort order" :value="$category->sort_order" col="col-md-2 mb-3" />
        <x-form.image name="banner" label="Banner image" :current="$category->banner_image ? storage_asset($category->banner_image) : null"
            ratio="16 / 7" help="JPG, PNG, WebP, GIF or JFIF, up to 2 MB. Wide images (about 1600×700 px) look best." col="col-md-6 mb-3" />
        <x-form.input name="seo_title" label="SEO title" :value="$category->seo_title" col="col-md-6 mb-3" />
        <x-form.input name="seo_description" label="SEO description" :value="$category->seo_description" col="col-md-6 mb-3" />
        <x-form.check name="status" label="Visible on website" :checked="$category->status" col="col-12" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Category</button></div>
</form>
@endsection
