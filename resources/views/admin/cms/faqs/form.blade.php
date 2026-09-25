@extends('layouts.admin')
@section('title', (string) ($faq->exists ? 'Edit FAQ' : 'New FAQ'))

@section('content')
<x-page-header :title="$faq->exists ? 'Edit FAQ' : 'New FAQ'" :back="route('admin.faqs.index')" />

<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="card" style="max-width: 800px" novalidate>
    @csrf
    @if ($faq->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="question" label="Question" :value="$faq->question" required col="col-12 mb-3" />
        <x-form.textarea name="answer" label="Answer" :value="$faq->answer" rows="4" required col="col-12 mb-3" />
        <x-form.select name="group" label="Group" :options="\App\Models\Faq::GROUPS" :value="$faq->group" col="col-md-6 mb-3" />
        <x-form.input name="sort_order" type="number" label="Order" :value="$faq->sort_order ?? 0" col="col-md-6 mb-3" />
        <x-form.check name="status" label="Visible on website" :checked="$faq->status" col="col-12" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
