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
                <thead><tr><th class="col-sl">#</th><th>Payment</th><th>Application</th><th>Method &amp; reference</th><th class="text-end">Amount &amp; status</th><th class="text-end">Invoice</th></tr></thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $payments->firstItem() + $loop->index }}</td>
                        <td data-label="Payment" class="text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ $payment->payment_no }}</span>
                            <small class="text-muted d-block">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</small>
                        </td>
                        <td data-label="Application" class="stack-multi td-clip" title="{{ $payment->application?->service->name }}">
                            @if ($payment->application)
                                <a href="{{ route('portal.applications.show', $payment->application) }}" class="d-block">{{ $payment->application->application_no }}</a>
                                <small class="text-muted d-block">{{ $payment->application->service->name }}</small>
                            @else
                                <span class="d-block">—</span>
                            @endif
                        </td>
                        <td data-label="Method &amp; reference" class="text-nowrap stack-multi" title="{{ $payment->methodLabel() }}">
                            <span class="d-block">{{ \Illuminate\Support\Str::before($payment->methodLabel(), ' (') }}</span>
                            <code class="pay-ref d-block">{{ $payment->transaction_id ?? '—' }}</code>
                        </td>
                        <td data-label="Amount &amp; status" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($payment->amount) }}</span>
                            <span class="d-block mt-1"><x-status-badge :status="$payment->status" /></span>
                        </td>
                        <td class="td-actions text-end text-nowrap">@if ($payment->invoice)<a href="{{ route('portal.invoices.pdf', $payment->invoice) }}" class="btn btn-sm btn-light"><i class="bi bi-file-earmark-pdf me-1"></i>Invoice</a>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$payments" label="payments" />
    @endif
</div>
@endsection
