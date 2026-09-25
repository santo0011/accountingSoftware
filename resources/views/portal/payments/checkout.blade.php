@extends('layouts.portal')
@section('title', (string) ('Payment'))

@section('content')
<x-page-header title="Complete your payment" :subtitle="'Application '.$application->application_no.' — '.$application->service->name" :back="route('portal.applications.show', $application)" />

@if ($awaitingVerification)
    <div class="alert alert-info"><i class="bi bi-hourglass-split me-1"></i>You submitted payment reference <strong>{{ $awaitingVerification->transaction_id }}</strong> on {{ $awaitingVerification->created_at->format('d M Y') }}. We are verifying it — you don't need to pay again.</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('portal.payments.pay', $application) }}" class="card" novalidate>
            @csrf
            <div class="card-header">Choose a payment method</div>
            <div class="card-body">
                @foreach ($gateways as $name => $gateway)
                    <div class="border rounded p-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gateway" id="gw_{{ $name }}" value="{{ $name }}" @checked(old('gateway', array_key_first($gateways)) === $name)
                                onchange="document.querySelectorAll('[data-gw]').forEach(el => el.classList.toggle('d-none', el.dataset.gw !== this.value))">
                            <label class="form-check-label fw-semibold text-navy" for="gw_{{ $name }}">{{ $gateway->label() }}</label>
                        </div>

                        @if ($name === 'manual')
                            <div data-gw="manual" class="mt-3 {{ old('gateway', array_key_first($gateways)) === 'manual' ? '' : 'd-none' }}">
                                <div class="bg-soft rounded p-3 small mb-3">
                                    <div class="fw-semibold text-navy mb-1">Pay {{ money($application->total) }} to:</div>
                                    <div style="white-space: pre-line">{{ setting('bank_details') }}</div>
                                </div>
                                <div class="row">
                                    <x-form.select name="method" label="Paid via" :options="['upi' => 'UPI', 'bank_transfer' => 'Bank transfer (NEFT/RTGS/IMPS)']" col="col-md-5 mb-3" />
                                    <x-form.input name="reference" label="UPI / UTR reference number" placeholder="e.g. 412345678901" help="Find this in your bank or UPI app after paying." col="col-md-7 mb-3" />
                                </div>
                            </div>
                        @elseif ($name === 'sandbox')
                            <div data-gw="sandbox" class="mt-2 small text-muted {{ old('gateway', array_key_first($gateways)) === 'sandbox' ? '' : 'd-none' }}">Test mode: the payment will be marked successful instantly. Disable in production (PAYMENT_SANDBOX_ENABLED=false).</div>
                        @endif
                    </div>
                @endforeach
                @error('gateway')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="small text-muted"><i class="bi bi-shield-lock me-1"></i>Secure payment</span>
                <button type="submit" class="btn btn-cta btn-lg">Pay {{ money($application->total) }}</button>
            </div>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between">Invoice summary @if ($application->invoice)<span class="small text-muted fw-normal">{{ $application->invoice->invoice_no }}</span>@endif</div>
            <div class="card-body">
                @php($invoice = $application->invoice)
                <dl class="dl-grid">
                    <dt>Service</dt><dd>{{ $application->service->name }}</dd>
                    <dt>Billed to</dt><dd>{{ $invoice?->billing_name ?? auth()->user()->name }}</dd>
                    <dt>Professional fee</dt><dd>{{ money($application->amount) }}</dd>
                    @if ((float) $application->discount > 0)<dt>Discount</dt><dd class="text-green">− {{ money($application->discount) }}</dd>@endif
                    @if ($invoice && (float) $invoice->igst > 0)
                        <dt>IGST</dt><dd>{{ money($invoice->igst) }}</dd>
                    @elseif ($invoice)
                        <dt>CGST</dt><dd>{{ money($invoice->cgst) }}</dd>
                        <dt>SGST</dt><dd>{{ money($invoice->sgst) }}</dd>
                    @else
                        <dt>GST</dt><dd>{{ money($application->tax) }}</dd>
                    @endif
                </dl>
                <div class="divider"></div>
                <div class="d-flex justify-content-between align-items-center"><strong class="text-navy">Total</strong><span class="fs-4 fw-bold text-navy">{{ money($application->total) }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
