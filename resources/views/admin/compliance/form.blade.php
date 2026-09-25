@extends('layouts.admin')
@section('title', (string) ($record->exists ? 'Edit Compliance' : 'Add Compliance'))

@section('content')
<x-page-header :title="$record->exists ? 'Edit compliance record' : 'Add compliance record'" :back="route('admin.compliance.index')" />

<form method="POST" action="{{ $record->exists ? route('admin.compliance.update', $record) : route('admin.compliance.store') }}" class="card" style="max-width: 900px" novalidate>
    @csrf
    @if ($record->exists) @method('PUT') @endif
    <div class="card-body row">
        <x-form.select name="customer_id" label="Customer" :options="$customers" :value="$record->customer_id" placeholder="Select customer" required col="col-md-6 mb-3" />
        <x-form.input name="business_id" type="number" label="Business ID (optional)" :value="$record->business_id" help="From the customer's profile." col="col-md-6 mb-3" />
        <x-form.select name="compliance_type_id" label="Compliance type" :options="$types" :value="$record->compliance_type_id" placeholder="Select" required col="col-md-6 mb-3" />
        <x-form.input name="title" label="Title (optional)" :value="$record->title" help="Defaults to the type name." col="col-md-6 mb-3" />
        <x-form.input name="period_label" label="Period" :value="$record->period_label" placeholder="e.g. Sep 2026 / Q2 FY 2026-27" col="col-md-4 mb-3" />
        <x-form.input name="due_date" type="date" label="Due date" :value="$record->due_date?->format('Y-m-d')" required col="col-md-4 mb-3" />
        <x-form.select name="assigned_staff_id" label="Assigned to" :options="$staff" :value="$record->assigned_staff_id" placeholder="Unassigned" col="col-md-4 mb-3" />
        <x-form.textarea name="remarks" label="Remarks" :value="$record->remarks" rows="2" col="col-12 mb-3" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
