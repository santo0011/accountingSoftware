@extends('layouts.admin')
@section('title', (string) ('Invoice '.$invoice->invoice_no))

@section('content')
<x-page-header :title="'Invoice '.$invoice->invoice_no" :back="route('admin.invoices.index')">
    <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="btn btn-light"><i class="bi bi-eye me-1"></i>Preview PDF</a>
    <a href="{{ route('admin.invoices.pdf', [$invoice, 'download' => 1]) }}" class="btn btn-primary"><i class="bi bi-download me-1"></i>Download</a>
</x-page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <div class="small text-muted">Billed to</div>
                        <div class="fw-semibold text-navy">{{ $invoice->billing_name }}</div>
                        <div class="small">{{ $invoice->billing_address }}</div>
                        @if ($invoice->billing_gstin)<div class="small">GSTIN: {{ $invoice->billing_gstin }}</div>@endif
                    </div>
                    <div class="text-end">
                        <x-status-badge :status="$invoice->status" class="mb-2" />
                        <div class="small">Date: <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong></div>
                        <div class="small">Place of supply: <strong>{{ $invoice->place_of_supply ?? '—' }}</strong></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Description</th><th>SAC</th><th class="text-end">Rate</th><th class="text-end">Discount</th><th class="text-end">GST</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                        @foreach ($invoice->items as $item)
                            <tr><td>{{ $item->description }}</td><td>{{ $item->sac_code }}</td><td class="text-end">{{ money($item->rate) }}</td><td class="text-end">{{ money($item->discount) }}</td><td class="text-end">{{ rtrim(rtrim(number_format($item->tax_rate, 2), '0'), '.') }}%</td><td class="text-end">{{ money($item->amount) }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row justify-content-end"><div class="col-md-6">
                    <dl class="dl-grid">
                        <dt>Subtotal</dt><dd class="text-end">{{ money($invoice->subtotal) }}</dd>
                        @if ((float) $invoice->discount > 0)<dt>Discount</dt><dd class="text-end">− {{ money($invoice->discount) }}</dd>@endif
                        @if ((float) $invoice->igst > 0)<dt>IGST</dt><dd class="text-end">{{ money($invoice->igst) }}</dd>
                        @else<dt>CGST</dt><dd class="text-end">{{ money($invoice->cgst) }}</dd><dt>SGST</dt><dd class="text-end">{{ money($invoice->sgst) }}</dd>@endif
                        <dt class="fw-bold text-navy">Total</dt><dd class="text-end fw-bold fs-5">{{ money($invoice->total) }}</dd>
                    </dl>
                </div></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4"><div class="card-body">
            <dl class="dl-grid">
                <dt>Customer</dt><dd><a href="{{ route('admin.customers.show', $invoice->customer) }}">{{ $invoice->customer->user->name }}</a></dd>
                @if ($invoice->application)<dt>Application</dt><dd><a href="{{ route('admin.applications.show', $invoice->application) }}">{{ $invoice->application->application_no }}</a></dd>@endif
            </dl>
        </div></div>
        <div class="card">
            <div class="card-header">Payments</div>
            @forelse ($invoice->payments as $p)
                <div class="list-row"><div class="flex-grow-1"><div class="title">{{ $p->payment_no }}</div><div class="meta">{{ $p->methodLabel() }} · {{ $p->transaction_id }}</div></div><strong>{{ money($p->amount) }}</strong><x-status-badge :status="$p->status" /></div>
            @empty
                <p class="small text-muted p-3 mb-0">No payments yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
