@extends('layouts.admin')
@section('title', (string) ($customer->exists ? 'Edit Customer' : 'Add Customer'))

@section('content')
<x-page-header :title="$customer->exists ? 'Edit '.$customer->user->name : 'Add customer'" :subtitle="$customer->exists ? 'Update their contact details, address or account status.' : 'Create a customer account — they can sign in to the portal with their mobile number.'" :back="$customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index')" />

<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" novalidate>
    @csrf
    @if ($customer->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Account details" text="Name and how they sign in." icon="bi-person-vcard">
                <x-form.input name="name" label="Full name" :value="$customer->user?->name" required placeholder="e.g. Aarav Patel" col="col-md-6 mb-3" />
                <x-form.input name="email" type="email" label="Email" :value="$customer->user?->email" required placeholder="name@example.com" col="col-md-6 mb-3" />
                <x-form.input name="mobile" label="Mobile (used to log in)" :value="$customer->user?->mobile" prepend="+91" maxlength="10" placeholder="10-digit mobile" col="col-md-4 mb-md-0 mb-3" />
                <x-form.input name="pan" label="PAN" :value="$customer->pan" maxlength="10" placeholder="ABCDE1234F" class="text-uppercase" col="col-md-4 mb-md-0 mb-3" />
                @if ($customer->exists)
                    <x-form.select name="status" label="Account status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked']" :value="$customer->user->status" col="col-md-4" />
                @else
                    <x-form.input name="source" label="Source" value="walk_in" help="e.g. walk_in, phone, referral" col="col-md-4" />
                @endif
            </x-form.section>

            <x-form.section title="Address" text="Used on invoices and for GST place of supply." icon="bi-geo-alt" tone="teal">
                <x-form.input name="address" label="Address" :value="$customer->address" placeholder="House / building, street, area" col="col-12 mb-3" />
                <x-form.input name="city" label="City" :value="$customer->city" col="col-md-4 mb-md-0 mb-3" />
                <x-form.input name="state" label="State" :value="$customer->state" col="col-md-4 mb-md-0 mb-3" />
                <x-form.input name="pincode" label="PIN code" :value="$customer->pincode" maxlength="6" col="col-md-4" />
            </x-form.section>

            @unless ($customer->exists)
                <x-form.section title="Primary business" text="Optional — you can add more businesses later from their profile." icon="bi-building" tone="violet">
                    <x-form.input name="business_name" label="Business name" placeholder="e.g. Patel Innovations Pvt Ltd" col="col-md-6 mb-3" />
                    <x-form.select name="business_type" label="Business type" :options="\App\Models\Business::TYPES" placeholder="Select" col="col-md-6 mb-3" />
                    <x-form.check name="send_invite" label="Email the customer a link to set their password" :checked="true" col="col-12" />
                </x-form.section>
            @endunless
        </div>

        <div class="col-xl-4">
            <x-form.tips title="Good to know">
                <li>Customers sign in to the portal with their <strong>mobile number</strong>.</li>
                <li>A unique <strong>customer ID</strong> (CUS-…) is created automatically.</li>
                @unless ($customer->exists)<li>Leave the invite ticked to email them a <strong>set-password link</strong>.</li>@endunless
                <li>The state decides whether invoices show CGST + SGST or IGST.</li>
            </x-form.tips>
        </div>
    </div>

    <x-form.actions :cancel="$customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index')" :label="$customer->exists ? 'Save changes' : 'Create customer'" />
</form>
@endsection
