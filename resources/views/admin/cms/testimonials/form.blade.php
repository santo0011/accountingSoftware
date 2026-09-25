@extends('layouts.admin')
@section('title', (string) ($testimonial->exists ? 'Edit Review' : 'New Review'))

@section('content')
<x-page-header :title="$testimonial->exists ? 'Edit review' : 'New review'" :back="route('admin.testimonials.index')" />

<form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="card" style="max-width: 800px" novalidate>
    @csrf
    @if ($testimonial->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Customer name" :value="$testimonial->name" required col="col-md-6 mb-3" />
        <x-form.input name="designation" label="Designation" :value="$testimonial->designation" col="col-md-6 mb-3" />
        <x-form.input name="company" label="Company" :value="$testimonial->company" col="col-md-6 mb-3" />
        <x-form.input name="city" label="City" :value="$testimonial->city" col="col-md-6 mb-3" />
        <x-form.textarea name="message" label="Review" :value="$testimonial->message" rows="4" required col="col-12 mb-3" />
        <x-form.select name="rating" label="Rating" :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" :value="$testimonial->rating" col="col-md-4 mb-3" />
        <x-form.input name="sort_order" type="number" label="Order" :value="$testimonial->sort_order ?? 0" col="col-md-4 mb-3" />
        <x-form.check name="status" label="Visible on website" :checked="$testimonial->status" col="col-12" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
