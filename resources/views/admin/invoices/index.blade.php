@extends('layouts.admin')
@section('title', (string) ('Invoices'))

@section('content')
<x-page-header title="Invoices" subtitle="GST invoices generated for applications." />

<div class="row g-3 mb-3">
    <div class="col-md-6"><x-stat-card label="Invoiced (filtered)" :value="money($totals->total ?? 0, false)" icon="bi-receipt" /></div>
    <div class="col-md-6"><x-stat-card label="GST collected (filtered)" :value="money($totals->tax ?? 0, false)" icon="bi-percent" color="teal" /></div>
</div>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Invoice no. or billing name"></div>
    <div class="col-md-2"><select name="status" class="form-select"><option value="">Any status</option>@foreach (\App\Enums\InvoiceStatus::options() as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
    <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    @if ($invoices->isEmpty())
        <x-empty-state icon="bi-receipt" title="No invoices found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Invoice</th><th>Billed to</th><th class="text-end">Amount</th><th>Status</th><th class="text-end">PDF</th></tr></thead>
                <tbody>
                @foreach ($invoices as $inv)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $invoices->firstItem() + $loop->index }}</td>
                        <td data-label="Invoice" class="text-nowrap stack-multi">
                            <a href="{{ route('admin.invoices.show', $inv) }}" class="fw-semibold d-block">{{ $inv->invoice_no }}</a>
                            <small class="text-muted d-block">{{ $inv->invoice_date->format('d M Y') }}</small>
                        </td>
                        <td data-label="Billed to" class="stack-multi td-clip" title="{{ $inv->billing_name }}">
                            <span class="d-block">{{ $inv->billing_name }}</span>
                            <small class="text-muted d-block">{{ $inv->billing_gstin ? 'GSTIN '.$inv->billing_gstin : ($inv->application?->application_no ?? 'No application') }}</small>
                        </td>
                        <td data-label="Amount" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($inv->total) }}</span>
                            <small class="text-muted d-block">{{ money($inv->subtotal - $inv->discount) }} + {{ money($inv->taxTotal()) }} GST</small>
                        </td>
                        <td data-label="Status"><x-status-badge :status="$inv->status" /></td>
                        <td class="td-actions text-end"><a href="{{ route('admin.invoices.pdf', $inv) }}" target="_blank" class="btn btn-sm btn-light" title="Download PDF" aria-label="PDF of {{ $inv->invoice_no }}"><i class="bi bi-file-earmark-pdf"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$invoices" label="invoices" />
    @endif
</div>
@endsection
