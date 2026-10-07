@extends('layouts.admin')
@section('title', (string) ($faq->exists ? 'Edit FAQ' : 'New FAQ'))

@section('content')
<x-page-header :title="$faq->exists ? 'Edit FAQ' : 'New FAQ'" subtitle="Questions and answers shown on the website's FAQ page." :back="route('admin.faqs.index')" />

<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" novalidate>
    @csrf
    @if ($faq->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Question & answer" text="Write it the way a customer would ask." icon="bi-question-circle">
                <x-form.input name="question" label="Question" :value="$faq->question" required placeholder="e.g. How long does company registration take?" col="col-12 mb-3" />
                <x-form.textarea name="answer" label="Answer" :value="$faq->answer" rows="5" required placeholder="Keep it short and clear — two or three sentences is ideal." col="col-12" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.section title="Display" text="Where and in what order it appears." icon="bi-eye" tone="teal">
                <x-form.select name="group" label="Group" :options="\App\Models\Faq::GROUPS" :value="$faq->group" col="col-12 mb-3" />
                <x-form.input name="sort_order" type="number" label="Order" :value="$faq->sort_order ?? 0" help="Lower numbers show first." col="col-12 mb-3" />
                <x-form.check name="status" label="Visible on website" :checked="$faq->status" col="col-12" />
            </x-form.section>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.faqs.index')" :label="$faq->exists ? 'Save changes' : 'Add FAQ'" />
</form>
@endsection
