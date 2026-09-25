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
                <thead><tr><th>Invoice</th><th>Date</th><th>Billed to</th><th>Application</th><th>Taxable</th><th>GST</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($invoices as $inv)
                    <tr>
                        <td data-label="Invoice"><a href="{{ route('admin.invoices.show', $inv) }}" class="fw-semibold">{{ $inv->invoice_no }}</a></td>
                        <td data-label="Date">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td data-label="Billed to">{{ $inv->billing_name }}@if ($inv->billing_gstin)<br><small class="text-muted">{{ $inv->billing_gstin }}</small>@endif</td>
                        <td data-label="Application">{{ $inv->application?->application_no ?? '—' }}</td>
                        <td data-label="Taxable">{{ money($inv->subtotal - $inv->discount) }}</td>
                        <td data-label="GST">{{ money($inv->taxTotal()) }}</td>
                        <td data-label="Total" class="fw-semibold">{{ money($inv->total) }}</td>
                        <td data-label="Status"><x-status-badge :status="$inv->status" /></td>
                        <td class="td-actions text-end"><a href="{{ route('admin.invoices.pdf', $inv) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-file-earmark-pdf"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $invoices->total() }} invoices</span>{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
