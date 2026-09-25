@extends('layouts.admin')
@section('title', (string) ($lead->exists ? 'Edit Lead' : 'Add Lead'))

@section('content')
<x-page-header :title="$lead->exists ? 'Edit lead' : 'Add lead'" :back="$lead->exists ? route('admin.leads.show', $lead) : route('admin.leads.index')" />

<form method="POST" action="{{ $lead->exists ? route('admin.leads.update', $lead) : route('admin.leads.store') }}" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($lead->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.input name="name" label="Name" :value="$lead->name" required col="col-md-6 mb-3" />
        <x-form.input name="company" label="Company" :value="$lead->company" col="col-md-6 mb-3" />
        <x-form.input name="phone" label="Phone" :value="$lead->phone" prepend="+91" maxlength="10" col="col-md-4 mb-3" />
        <x-form.input name="email" type="email" label="Email" :value="$lead->email" col="col-md-4 mb-3" />
        <x-form.input name="city" label="City" :value="$lead->city" col="col-md-4 mb-3" />
        <x-form.select name="service_id" label="Service interested in" :options="$services" :value="$lead->service_id" placeholder="Not sure" col="col-md-6 mb-3" />
        <x-form.select name="source" label="Source" :options="$sources" :value="$lead->source" required col="col-md-3 mb-3" />
        @if ($lead->exists)
            <x-form.select name="status" label="Status" :options="$statuses" :value="$lead->status" col="col-md-3 mb-3" />
        @endif
        <x-form.select name="assigned_to" label="Assigned to" :options="$staff" :value="$lead->assigned_to" placeholder="Unassigned" col="col-md-6 mb-3" />
        <x-form.input name="next_followup_at" type="date" label="Next follow-up" :value="$lead->next_followup_at?->format('Y-m-d')" col="col-md-6 mb-3" />
        <x-form.textarea name="message" label="Enquiry / requirement" :value="$lead->message" col="col-md-6 mb-3" />
        <x-form.textarea name="notes" label="Internal notes" :value="$lead->notes" col="col-md-6 mb-3" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">{{ $lead->exists ? 'Save Changes' : 'Create Lead' }}</button></div>
</form>
@endsection
