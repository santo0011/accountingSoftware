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
        <div class="col-md-6 mb-3">
            <label class="form-label">Banner image</label>
            <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/*">
            @error('banner')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($category->banner_image)<img src="{{ storage_asset($category->banner_image) }}" alt="" class="mt-2 rounded" style="max-height:60px">@endif
        </div>
        <x-form.input name="seo_title" label="SEO title" :value="$category->seo_title" col="col-md-6 mb-3" />
        <x-form.input name="seo_description" label="SEO description" :value="$category->seo_description" col="col-md-6 mb-3" />
        <x-form.check name="status" label="Visible on website" :checked="$category->status" col="col-12" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Category</button></div>
</form>
@endsection
