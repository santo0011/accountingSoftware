@extends('layouts.admin')
@section('title', (string) ('Settings'))

@php($v = fn ($key, $default = null) => old($key, $s[$key] ?? $default))

@section('content')
<x-page-header title="Settings" subtitle="Company details, billing, notifications, email and SEO." />

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" novalidate>
    @csrf @method('PUT')

    <ul class="nav nav-tabs mb-3" role="tablist">
        @foreach (['company' => 'Company', 'billing' => 'Billing & numbering', 'notifications' => 'Notifications & email', 'seo' => 'SEO & social', 'homepage' => 'Homepage'] as $id => $label)
            <li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab" data-bs-target="#{{ $id }}">{{ $label }}</button></li>
        @endforeach
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="company">
            <div class="card"><div class="card-body row">
                <x-form.input name="company_name" label="Company / brand name" :value="$v('company_name')" required col="col-md-6 mb-3" />
                <x-form.input name="company_legal_name" label="Legal name (on invoices)" :value="$v('company_legal_name')" col="col-md-6 mb-3" />
                <x-form.input name="tagline" label="Tagline" :value="$v('tagline')" col="col-md-12 mb-3" />
                <x-form.input name="company_email" type="email" label="Email" :value="$v('company_email')" required col="col-md-4 mb-3" />
                <x-form.input name="company_phone" label="Phone" :value="$v('company_phone')" required col="col-md-4 mb-3" />
                <x-form.input name="company_whatsapp" label="WhatsApp (with country code, digits only)" :value="$v('company_whatsapp')" placeholder="919876543210" col="col-md-4 mb-3" />
                <x-form.input name="company_address" label="Address" :value="$v('company_address')" col="col-md-8 mb-3" />
                <x-form.input name="company_state" label="State (for GST)" :value="$v('company_state')" required help="Same-state customers get CGST+SGST, others IGST." col="col-md-4 mb-3" />
                <x-form.input name="company_gstin" label="GSTIN" :value="$v('company_gstin')" col="col-md-4 mb-3" />
                <x-form.input name="company_pan" label="PAN" :value="$v('company_pan')" col="col-md-4 mb-3" />
                <x-form.input name="business_hours" label="Business hours" :value="$v('business_hours')" col="col-md-4 mb-3" />
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if (! empty($s['logo']))<img src="{{ storage_asset($s['logo']) }}" alt="Logo" class="mt-2" style="max-height:40px">@else<div class="form-text">No logo uploaded — the text logo is used.</div>@endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Favicon (PNG/ICO)</label>
                    <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept=".png,.ico">
                    @error('favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="billing">
            <div class="card"><div class="card-body row">
                <x-form.input name="currency" label="Currency" :value="$v('currency', 'INR')" required col="col-md-3 mb-3" />
                <x-form.input name="currency_symbol" label="Symbol" :value="$v('currency_symbol', '₹')" required col="col-md-3 mb-3" />
                <x-form.select name="default_tax" label="Default GST rate" :options="[0 => '0%', 5 => '5%', 12 => '12%', 18 => '18%', 28 => '28%']" :value="(int) $v('default_tax', 18)" col="col-md-3 mb-3" />
                <x-form.input name="default_sac_code" label="Default SAC code" :value="$v('default_sac_code')" col="col-md-3 mb-3" />
                <x-form.input name="application_prefix" label="Application prefix" :value="$v('application_prefix', 'APP')" required help="APP-2026-000001" col="col-md-3 mb-3" />
                <x-form.input name="invoice_prefix" label="Invoice prefix" :value="$v('invoice_prefix', 'INV')" required col="col-md-3 mb-3" />
                <x-form.input name="payment_prefix" label="Payment prefix" :value="$v('payment_prefix', 'PAY')" required col="col-md-3 mb-3" />
                <x-form.input name="ticket_prefix" label="Ticket prefix" :value="$v('ticket_prefix', 'TKT')" required col="col-md-3 mb-3" />
                <x-form.textarea name="bank_details" label="Bank / UPI details (shown on checkout & invoice)" :value="$v('bank_details')" rows="5" col="col-md-6 mb-3" />
                <x-form.textarea name="invoice_terms" label="Invoice terms" :value="$v('invoice_terms')" rows="5" col="col-md-6 mb-3" />
            </div></div>
        </div>

        <div class="tab-pane fade" id="notifications">
            <div class="card mb-3"><div class="card-body row">
                <x-form.check name="notify_email_enabled" label="Send email notifications (in addition to in-app notifications)" :checked="$v('notify_email_enabled', '1') === '1'" col="col-12 mb-3" />
                <x-form.input name="compliance_reminder_days" type="number" label="Compliance reminder (days before due date)" :value="$v('compliance_reminder_days', 7)" required col="col-md-6 mb-3" />
                <x-form.input name="admin_notification_email" type="email" label="Operations email" :value="$v('admin_notification_email')" col="col-md-6 mb-3" />
            </div></div>
            <div class="card"><div class="card-header">SMTP email server <span class="small text-muted fw-normal">— leave blank to use the .env mail settings</span></div><div class="card-body row">
                <x-form.input name="mail_host" label="SMTP host" :value="$v('mail_host')" placeholder="smtp.gmail.com" col="col-md-6 mb-3" />
                <x-form.input name="mail_port" type="number" label="Port" :value="$v('mail_port')" placeholder="587" col="col-md-3 mb-3" />
                <x-form.select name="mail_encryption" label="Encryption" :options="['tls' => 'TLS', 'ssl' => 'SSL']" :value="$v('mail_encryption')" placeholder="None" col="col-md-3 mb-3" />
                <x-form.input name="mail_username" label="Username" :value="$v('mail_username')" autocomplete="off" col="col-md-4 mb-3" />
                <x-form.input name="mail_password" type="password" label="Password" autocomplete="new-password" :help="! empty($s['mail_password']) ? 'Saved — leave blank to keep it.' : null" col="col-md-4 mb-3" />
                <x-form.input name="mail_from_address" type="email" label="From address" :value="$v('mail_from_address')" col="col-md-4 mb-3" />
            </div></div>
        </div>

        <div class="tab-pane fade" id="seo">
            <div class="card mb-3"><div class="card-body row">
                <x-form.input name="seo_title" label="Homepage title" :value="$v('seo_title')" col="col-12 mb-3" />
                <x-form.textarea name="seo_description" label="Default meta description" :value="$v('seo_description')" rows="2" col="col-12 mb-3" />
                <x-form.input name="seo_keywords" label="Keywords" :value="$v('seo_keywords')" col="col-md-8 mb-3" />
                <x-form.input name="google_analytics_id" label="Google Analytics ID" :value="$v('google_analytics_id')" placeholder="G-XXXXXXX" col="col-md-4 mb-3" />
            </div></div>
            <div class="card"><div class="card-header">Social links</div><div class="card-body row">
                @foreach (['facebook', 'linkedin', 'instagram', 'x', 'youtube'] as $net)
                    <x-form.input :name="'social_'.$net" type="url" :label="ucfirst($net === 'x' ? 'X (Twitter)' : $net)" :value="$v('social_'.$net)" col="col-md-6 mb-3" />
                @endforeach
            </div></div>
        </div>

        <div class="tab-pane fade" id="homepage">
            <div class="card"><div class="card-body row">
                <x-form.input name="stat_customers" label="Happy customers" :value="$v('stat_customers')" col="col-md-3 mb-3" />
                <x-form.input name="stat_services" label="Services" :value="$v('stat_services')" col="col-md-3 mb-3" />
                <x-form.input name="stat_experts" label="Experts" :value="$v('stat_experts')" col="col-md-3 mb-3" />
                <x-form.input name="stat_rating" label="Rating" :value="$v('stat_rating')" col="col-md-3 mb-3" />
            </div></div>
        </div>
    </div>

    <div class="text-end mt-3"><button class="btn btn-primary btn-lg">Save Settings</button></div>
</form>
@endsection
