@extends('layouts.admin')
@section('title', (string) ('New Application'))

@section('content')
<x-page-header title="New application" subtitle="Create an application on behalf of a customer (e.g. from a phone enquiry)." :back="route('admin.applications.index')" />

<form method="POST" action="{{ route('admin.applications.store') }}" novalidate>
    @csrf
    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Customer & service" text="Who the application is for and what they need." icon="bi-folder-plus">
                <x-form.select name="customer_id" label="Customer" :options="$customers" :value="$selectedCustomer" placeholder="Select customer" required col="col-md-6 mb-3"
                    help="Customer not listed? Create the customer first." />
                <x-form.select name="service_id" label="Service" :options="$services" placeholder="Select service" required col="col-md-6 mb-3" />
                <x-form.textarea name="notes" label="Requirement notes" rows="4" placeholder="What the customer asked for, documents they already have, any deadline…" col="col-12" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.tips title="What happens next" icon="bi-signpost-split">
                <li>An <strong>unpaid GST invoice</strong> is generated automatically from the service price.</li>
                <li>You are assigned as the <strong>relationship manager</strong> — you can reassign it later.</li>
                <li>The customer sees the application in their portal and can upload documents and pay.</li>
            </x-form.tips>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.applications.index')" label="Create application" icon="bi-plus-lg">
        @can('customers.create')<a href="{{ route('admin.customers.create') }}" class="btn btn-light me-auto"><i class="bi bi-person-plus me-1"></i>New customer</a>@endcan
    </x-form.actions>
</form>
@endsection
