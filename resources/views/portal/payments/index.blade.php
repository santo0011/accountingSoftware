@extends('layouts.portal')
@section('title', (string) ('Payments'))

@section('content')
<x-page-header title="Payments" subtitle="Pending dues and your payment history." />

@if ($dues->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header">Pending payments</div>
        @foreach ($dues as $app)
            @php($verifying = $app->payments->first(fn ($p) => $p->status === \App\Enums\PaymentStatus::Pending && $p->transaction_id))
            <div class="list-row">
                <span class="icon-bubble sm amber"><i class="bi bi-hourglass-split"></i></span>
                <div class="min-w-0 flex-grow-1">
                    <div class="title">{{ $app->service->name }}</div>
                    <div class="meta">{{ $app->application_no }} · {{ $app->created_at->format('d M Y') }}</div>
                </div>
                <div class="fw-bold text-navy me-2">{{ money($app->total) }}</div>
                @if ($verifying)
                    <span class="badge badge-soft-info">Verifying ref. {{ $verifying->transaction_id }}</span>
                @else
                    <a href="{{ route('portal.payments.checkout', $app) }}" class="btn btn-sm btn-cta">Pay Now</a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<div class="table-card">
    <div class="card-header bg-white">Payment history</div>
    @if ($payments->isEmpty())
        <x-empty-state icon="bi-credit-card" title="No payments yet" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Payment</th><th>Application</th><th>Method</th><th>Reference</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $payments->firstItem() + $loop->index }}</td>
                        <td data-label="Payment" class="fw-semibold">{{ $payment->payment_no }}</td>
                        <td data-label="Application">@if ($payment->application)<a href="{{ route('portal.applications.show', $payment->application) }}">{{ $payment->application->application_no }}</a><br><small class="text-muted">{{ $payment->application->service->name }}</small>@else — @endif</td>
                        <td data-label="Method">{{ $payment->methodLabel() }}</td>
                        <td data-label="Reference"><code>{{ $payment->transaction_id ?? '—' }}</code></td>
                        <td data-label="Date">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</td>
                        <td data-label="Amount" class="fw-semibold">{{ money($payment->amount) }}</td>
                        <td data-label="Status"><x-status-badge :status="$payment->status" /></td>
                        <td class="td-actions text-end">@if ($payment->invoice)<a href="{{ route('portal.invoices.pdf', $payment->invoice) }}" class="btn btn-sm btn-light"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$payments" label="payments" />
    @endif
</div>
@endsection
