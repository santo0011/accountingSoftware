@extends('layouts.admin')
@section('title', (string) ($testimonial->exists ? 'Edit Review' : 'New Review'))

@section('content')
<x-page-header :title="$testimonial->exists ? 'Edit review' : 'New review'" subtitle="Customer reviews shown on the website." :back="route('admin.testimonials.index')" />

<form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" novalidate>
    @csrf
    @if ($testimonial->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Reviewer" text="Who wrote the review." icon="bi-person-badge">
                <x-form.input name="name" label="Customer name" :value="$testimonial->name" required placeholder="e.g. Ananya Bose" col="col-md-6 mb-3" />
                <x-form.input name="designation" label="Designation" :value="$testimonial->designation" placeholder="e.g. Founder" col="col-md-6 mb-3" />
                <x-form.input name="company" label="Company" :value="$testimonial->company" col="col-md-6 mb-md-0 mb-3" />
                <x-form.input name="city" label="City" :value="$testimonial->city" col="col-md-6" />
            </x-form.section>

            <x-form.section title="Review" text="Their words, as they said them." icon="bi-chat-quote" tone="violet">
                <x-form.textarea name="message" label="Review" :value="$testimonial->message" rows="5" required col="col-12" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.section title="Display" text="Rating and position on the website." icon="bi-star" tone="amber">
                <x-form.select name="rating" label="Rating" :options="[5 => '★★★★★  (5)', 4 => '★★★★  (4)', 3 => '★★★  (3)', 2 => '★★  (2)', 1 => '★  (1)']" :value="$testimonial->rating" col="col-12 mb-3" />
                <x-form.input name="sort_order" type="number" label="Order" :value="$testimonial->sort_order ?? 0" help="Lower numbers show first." col="col-12 mb-3" />
                <x-form.check name="status" label="Visible on website" :checked="$testimonial->status" col="col-12" />
            </x-form.section>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.testimonials.index')" :label="$testimonial->exists ? 'Save changes' : 'Add review'" />
</form>
@endsection
