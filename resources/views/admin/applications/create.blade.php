@extends('layouts.admin')
@section('title', (string) ('New Application'))

@section('content')
<x-page-header title="New application" subtitle="Create an application on behalf of a customer (e.g. from a phone enquiry)." :back="route('admin.applications.index')" />

<div class="row"><div class="col-lg-7">
    <form method="POST" action="{{ route('admin.applications.store') }}" class="card" novalidate>
        @csrf
        <div class="card-body">
            <x-form.select name="customer_id" label="Customer" :options="$customers" :value="$selectedCustomer" placeholder="Select customer" required
                help="Customer not listed? Create the customer first." />
            <x-form.select name="service_id" label="Service" :options="$services" placeholder="Select service" required />
            <x-form.textarea name="notes" label="Requirement notes" rows="3" />
            <div class="small text-muted">An unpaid GST invoice is generated automatically. You'll be assigned as the relationship manager.</div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            @can('customers.create')<a href="{{ route('admin.customers.create') }}" class="btn btn-light">+ New Customer</a>@endcan
            <button class="btn btn-primary">Create Application</button>
        </div>
    </form>
</div></div>
@endsection
