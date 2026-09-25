@extends('layouts.portal')
@section('title', (string) ('New Support Ticket'))

@section('content')
<x-page-header title="New support ticket" :back="route('portal.support.index')" />

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('portal.support.store') }}" enctype="multipart/form-data" class="card" novalidate>
            @csrf
            <div class="card-body">
                <x-form.input name="subject" label="Subject" required maxlength="150" />
                <div class="row">
                    <x-form.select name="category" label="Category" :options="\App\Models\SupportTicket::CATEGORIES" required col="col-md-4 mb-3" />
                    <x-form.select name="priority" label="Priority" :options="\App\Models\SupportTicket::PRIORITIES" value="medium" required col="col-md-4 mb-3" />
                    <x-form.select name="application_id" label="Related application" :options="$applications" :value="$selected" placeholder="None" col="col-md-4 mb-3" />
                </div>
                <x-form.textarea name="message" label="Message" rows="6" required />
                <x-form.input name="attachment" type="file" label="Attachment (optional)" help="PDF, JPG, PNG or DOC up to 5 MB." accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-cta">Submit Ticket</button></div>
        </form>
    </div>
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card"><div class="card-body small">
            <div class="fw-semibold text-navy mb-2">Prefer to talk?</div>
            <p class="text-muted">Call us at <a href="tel:{{ preg_replace('/\s+/', '', setting('company_phone')) }}">{{ setting('company_phone') }}</a> ({{ setting('business_hours') }}) or email <a href="mailto:{{ setting('company_email') }}">{{ setting('company_email') }}</a>.</p>
        </div></div>
    </div>
</div>
@endsection
