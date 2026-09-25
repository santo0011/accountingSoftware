@extends('layouts.admin')
@section('title', (string) ('Payments'))

@section('content')
<x-page-header title="Payments" subtitle="Verify customer payments, record offline payments and process refunds." />

<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card label="Paid (filtered)" :value="money($totals['paid'], false)" icon="bi-check2-circle" color="green" /></div>
    <div class="col-md-4"><x-stat-card label="Pending verification" :value="money($totals['pending'], false)" icon="bi-hourglass-split" color="amber" /></div>
    <div class="col-md-4"><x-stat-card label="Refunded" :value="money($totals['refunded'], false)" icon="bi-arrow-counterclockwise" color="red" /></div>
</div>

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Payment no., reference, application, customer"></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Any status</option>@foreach (\App\Enums\PaymentStatus::options() as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="method" class="form-select"><option value="">Any method</option>@foreach (\App\Models\Payment::METHODS as $v => $l)<option value="{{ $v }}" @selected(request('method') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From"></div>
    <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100" data-no-lock>Go</button></div>
</form>

<div class="table-card">
    @if ($payments->isEmpty())
        <x-empty-state icon="bi-credit-card" title="No payments found" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th>Payment</th><th>Customer</th><th>Application</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($payments as $p)
                    <tr>
                        <td data-label="Payment" class="fw-semibold">{{ $p->payment_no }}<br><small class="text-muted fw-normal">{{ ($p->paid_at ?? $p->created_at)->format('d M Y') }}</small></td>
                        <td data-label="Customer">{{ $p->customer->user->name }}</td>
                        <td data-label="Application">@if ($p->application)<a href="{{ route('admin.applications.show', $p->application) }}">{{ $p->application->application_no }}</a>@else — @endif</td>
                        <td data-label="Method">{{ $p->methodLabel() }}<br><small class="text-muted">{{ ucfirst($p->gateway) }}</small></td>
                        <td data-label="Reference"><code>{{ $p->transaction_id ?? '—' }}</code></td>
                        <td data-label="Amount" class="fw-semibold">{{ money($p->amount) }}@if ((float) $p->refunded_amount > 0)<br><small class="text-danger">− {{ money($p->refunded_amount) }}</small>@endif</td>
                        <td data-label="Status"><x-status-badge :status="$p->status" /></td>
                        <td class="td-actions text-end text-nowrap">
                            @if ($p->status === \App\Enums\PaymentStatus::Pending)
                                @can('payments.manage')
                                    <form method="POST" action="{{ route('admin.payments.confirm', $p) }}" class="d-inline" data-confirm="Confirm you received {{ money($p->amount) }} (ref {{ $p->transaction_id }})?">@csrf<button class="btn btn-sm btn-cta">Confirm</button></form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#failModal" data-action="{{ route('admin.payments.fail', $p) }}">Reject</button>
                                @endcan
                            @elseif ($p->status === \App\Enums\PaymentStatus::Paid)
                                @can('payments.refund')
                                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#refundModal" data-action="{{ route('admin.payments.refund', $p) }}" data-amount="{{ $p->amount }}">Refund</button>
                                @endcan
                            @endif
                            @if ($p->invoice)<a href="{{ route('admin.invoices.show', $p->invoice) }}" class="btn btn-sm btn-light" title="Invoice"><i class="bi bi-receipt"></i></a>@endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $payments->total() }} payments</span>{{ $payments->links() }}</div>
    @endif
</div>

<div class="modal fade" id="failModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Reject payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Reason</label><input type="text" name="reason" class="form-control" required placeholder="e.g. Reference not found in bank statement"></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Reject Payment</button></div>
</form></div></div>

<div class="modal fade" id="refundModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Refund payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Refund amount</label><input type="number" step="0.01" min="1" name="amount" class="form-control mb-3" required>
        <label class="form-label">Reason</label><input type="text" name="reason" class="form-control" required>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Record Refund</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
    ['failModal', 'refundModal'].forEach((id) => document.getElementById(id).addEventListener('show.bs.modal', (e) => {
        const btn = e.relatedTarget, form = e.target.querySelector('form');
        form.action = btn.dataset.action;
        if (btn.dataset.amount) form.querySelector('[name=amount]').value = btn.dataset.amount;
    }));
</script>
@endpush
