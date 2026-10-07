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
                <thead><tr><th class="col-sl">#</th><th>Payment</th><th>Customer &amp; application</th><th>Method &amp; reference</th><th class="text-end">Amount &amp; status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($payments as $p)
                    @php($method = \Illuminate\Support\Str::before($p->methodLabel(), ' ('))
                    <tr>
                        <td class="col-sl" data-label="#">{{ $payments->firstItem() + $loop->index }}</td>
                        <td data-label="Payment" class="text-nowrap stack-multi">
                            <span class="fw-semibold text-navy d-block">{{ $p->payment_no }}</span>
                            <small class="text-muted d-block">{{ ($p->paid_at ?? $p->created_at)->format('d M Y') }}</small>
                        </td>
                        <td data-label="Customer &amp; application" class="stack-multi pay-cust">
                            <span class="d-block text-truncate">{{ $p->customer->user->name }}</span>
                            @if ($p->application)<a href="{{ route('admin.applications.show', $p->application) }}" class="small d-block text-nowrap">{{ $p->application->application_no }}</a>@else<small class="text-muted d-block">No application</small>@endif
                        </td>
                        <td data-label="Method &amp; reference" class="stack-multi" title="{{ $p->methodLabel() }} · {{ ucfirst($p->gateway) }}">
                            <span class="d-block text-nowrap">{{ $method }}</span>
                            <code class="pay-ref d-block text-nowrap">{{ $p->transaction_id ?? '—' }}</code>
                        </td>
                        <td data-label="Amount &amp; status" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($p->amount) }}</span>
                            @if ((float) $p->refunded_amount > 0)<small class="text-danger d-block">− {{ money($p->refunded_amount) }} refunded</small>@endif
                            <span class="d-block mt-1"><x-status-badge :status="$p->status" /></span>
                        </td>
                        <td class="td-actions text-end text-nowrap">
                            <span class="pay-actions">
                                @if ($p->status === \App\Enums\PaymentStatus::Pending)
                                    @can('payments.manage')
                                        <form method="POST" action="{{ route('admin.payments.confirm', $p) }}" class="d-inline" data-confirm="Confirm you received {{ money($p->amount) }} (ref {{ $p->transaction_id }})?">@csrf<button class="btn btn-sm btn-cta" title="Confirm payment received"><i class="bi bi-check2 me-1"></i>Confirm</button></form>
                                        <button class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#failModal" data-action="{{ route('admin.payments.fail', $p) }}" title="Reject payment" aria-label="Reject payment {{ $p->payment_no }}"><i class="bi bi-x-lg"></i></button>
                                    @endcan
                                @elseif ($p->status === \App\Enums\PaymentStatus::Paid)
                                    @can('payments.refund')
                                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#refundModal" data-action="{{ route('admin.payments.refund', $p) }}" data-amount="{{ $p->amount }}" title="Record a refund"><i class="bi bi-arrow-counterclockwise me-1"></i>Refund</button>
                                    @endcan
                                @endif
                                @if ($p->invoice)
                                    <a href="{{ route('admin.invoices.show', $p->invoice) }}" class="btn btn-sm btn-light" title="View invoice" aria-label="Invoice for {{ $p->payment_no }}"><i class="bi bi-receipt"></i></a>
                                @else
                                    <span class="btn btn-sm btn-light invisible" aria-hidden="true"><i class="bi bi-receipt"></i></span>
                                @endif
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$payments" label="payments" />
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
    // Delegated so it keeps working after live filtering replaces the table and modals.
    document.addEventListener('show.bs.modal', (e) => {
        if (!['failModal', 'refundModal'].includes(e.target.id)) return;
        const btn = e.relatedTarget, form = e.target.querySelector('form');
        form.action = btn.dataset.action;
        if (btn.dataset.amount) form.querySelector('[name=amount]').value = btn.dataset.amount;
    });
</script>
@endpush
