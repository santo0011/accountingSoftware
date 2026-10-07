@extends('layouts.portal')
@section('title', (string) ('Invoices'))

@section('content')
<x-page-header title="Invoices" subtitle="Download GST invoices for all your services." />

<div class="table-card">
    @if ($invoices->isEmpty())
        <x-empty-state icon="bi-receipt" title="No invoices yet" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Invoice</th><th>Service &amp; billed to</th><th class="text-end">Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $invoices->firstItem() + $loop->index }}</td>
                        <td data-label="Invoice" class="text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ $invoice->invoice_no }}</span>
                            <small class="text-muted d-block">{{ $invoice->invoice_date->format('d M Y') }}</small>
                        </td>
                        <td data-label="Service &amp; billed to" class="stack-multi td-clip wide" title="{{ $invoice->application?->service?->name }} · {{ $invoice->billing_name }}">
                            <span class="d-block">{{ $invoice->application?->service?->name ?? '—' }}</span>
                            <small class="text-muted d-block">{{ $invoice->billing_name }}</small>
                        </td>
                        <td data-label="Amount" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($invoice->total) }}</span>
                            <small class="text-muted d-block">incl. {{ money($invoice->taxTotal()) }} GST</small>
                        </td>
                        <td data-label="Status" class="text-nowrap"><x-status-badge :status="$invoice->status" /></td>
                        <td class="td-actions text-end text-nowrap">
                            @if ($invoice->status === \App\Enums\InvoiceStatus::Unpaid && $invoice->application && auth()->user()->can('pay', $invoice->application))
                                <a href="{{ route('portal.payments.checkout', $invoice->application) }}" class="btn btn-sm btn-cta">Pay</a>
                            @endif
                            <a href="{{ route('portal.invoices.pdf', $invoice) }}" class="btn btn-sm btn-light"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$invoices" label="invoices" />
    @endif
</div>
@endsection
