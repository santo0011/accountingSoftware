@extends('layouts.admin')
@section('title', (string) ($record->exists ? 'Edit Compliance' : 'Add Compliance'))

@section('content')
<x-page-header :title="$record->exists ? 'Edit compliance record' : 'Add compliance record'" :subtitle="$record->exists ? 'Update the due date, owner or remarks.' : 'Track a filing deadline for a customer.'" :back="route('admin.compliance.index')" />

<form method="POST" action="{{ $record->exists ? route('admin.compliance.update', $record) : route('admin.compliance.store') }}" novalidate>
    @csrf
    @if ($record->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Customer & compliance" text="Which filing, and for whom." icon="bi-calendar-check">
                <x-form.select name="customer_id" label="Customer" :options="$customers" :value="$record->customer_id" placeholder="Select customer" required col="col-md-6 mb-3" />
                <x-form.input name="business_id" type="number" label="Business ID (optional)" :value="$record->business_id" help="From the customer's profile." col="col-md-6 mb-3" />
                <x-form.select name="compliance_type_id" label="Compliance type" :options="$types" :value="$record->compliance_type_id" placeholder="Select" required col="col-md-6 mb-md-0 mb-3" />
                <x-form.input name="title" label="Title (optional)" :value="$record->title" help="Defaults to the type name." col="col-md-6" />
            </x-form.section>

            <x-form.section title="Period & due date" text="When it is due and who files it." icon="bi-alarm" tone="amber">
                <x-form.input name="period_label" label="Period" :value="$record->period_label" placeholder="e.g. Sep 2026 / Q2 FY 2026-27" col="col-md-6 mb-3" />
                <div class="col-md-6 mb-3">
                    <x-form.input name="due_date" type="date" label="Due date" :value="$record->due_date?->format('Y-m-d')" required col="" />
                    <div class="quick-dates" data-for="f_due_date" role="group" aria-label="Quick due dates">
                        <button type="button" data-days="7">In 1 week</button>
                        <button type="button" data-days="15">In 15 days</button>
                        <button type="button" data-days="30">In 30 days</button>
                    </div>
                </div>
                <x-form.select name="assigned_staff_id" label="Assigned to" :options="$staff" :value="$record->assigned_staff_id" placeholder="Unassigned" col="col-md-6 mb-3" />
                <x-form.textarea name="remarks" label="Remarks" :value="$record->remarks" rows="2" placeholder="Anything to remember for this filing" col="col-12" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.tips title="Good to know">
                <li>Records for <strong>recurring services</strong> are created automatically — add manual ones for anything else.</li>
                <li>The customer gets a <strong>reminder</strong> before the due date.</li>
                <li>Mark it <strong>Done</strong> from the compliance list once filed.</li>
            </x-form.tips>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.compliance.index')" :label="$record->exists ? 'Save changes' : 'Add record'" />
</form>
@endsection
