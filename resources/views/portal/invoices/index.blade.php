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
                <thead><tr><th class="col-sl">#</th><th>Invoice</th><th>Date</th><th>Service</th><th>Billed to</th><th>Tax</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $invoices->firstItem() + $loop->index }}</td>
                        <td data-label="Invoice" class="fw-semibold">{{ $invoice->invoice_no }}</td>
                        <td data-label="Date">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td data-label="Service">{{ $invoice->application?->service?->name ?? '—' }}</td>
                        <td data-label="Billed to">{{ $invoice->billing_name }}</td>
                        <td data-label="Tax">{{ money($invoice->taxTotal()) }}</td>
                        <td data-label="Total" class="fw-semibold">{{ money($invoice->total) }}</td>
                        <td data-label="Status"><x-status-badge :status="$invoice->status" /></td>
                        <td class="td-actions text-end">
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
