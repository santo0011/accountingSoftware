@extends('layouts.admin')
@section('title', (string) ($customer->user->name))

@section('content')
<x-page-header :title="$customer->user->name" :subtitle="$customer->customer_code.' · Customer since '.$customer->created_at->format('M Y')" :back="route('admin.customers.index')">
    @can('applications.create')<a href="{{ route('admin.applications.create', ['customer' => $customer->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Application</a>@endcan
    @can('compliance.manage')<a href="{{ route('admin.compliance.create', ['customer' => $customer->id]) }}" class="btn btn-light"><i class="bi bi-calendar-plus me-1"></i>Add Compliance</a>@endcan
    @can('customers.edit')<a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><x-stat-card label="Applications" :value="$customer->applications->count()" icon="bi-folder2-open" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Active services" :value="$customer->customerServices->where('status', 'active')->count()" icon="bi-briefcase" color="teal" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Total billed" :value="money($totals['billed'], false)" icon="bi-receipt" color="amber" /></div>
    <div class="col-6 col-lg-3"><x-stat-card label="Total paid" :value="money($totals['paid'], false)" icon="bi-currency-rupee" color="green" /></div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">Contact</div>
            <div class="card-body">
                <dl class="dl-grid">
                    <dt>Email</dt><dd><a href="mailto:{{ $customer->user->email }}">{{ $customer->user->email }}</a></dd>
                    <dt>Mobile</dt><dd>{{ $customer->user->mobile ?? '—' }}</dd>
                    <dt>PAN</dt><dd>{{ $customer->pan ?? '—' }}</dd>
                    <dt>Address</dt><dd>{{ collect([$customer->address, $customer->city, $customer->state, $customer->pincode])->filter()->implode(', ') ?: '—' }}</dd>
                    <dt>Source</dt><dd>{{ ucfirst(str_replace('_', ' ', $customer->source ?? '—')) }}</dd>
                    <dt>Status</dt><dd><span class="badge badge-soft-{{ $customer->user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($customer->user->status) }}</span></dd>
                    <dt>Last login</dt><dd>{{ $customer->user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                    <dt>Email verified</dt><dd>{{ $customer->user->email_verified_at ? 'Yes' : 'No' }}</dd>
                </dl>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Businesses</div>
            @forelse ($customer->businesses as $b)
                <div class="list-row"><span class="icon-bubble sm"><i class="bi bi-building"></i></span><div class="min-w-0"><div class="title">{{ $b->name }} @if ($b->is_primary)<span class="badge badge-soft-primary">Primary</span>@endif</div><div class="meta">{{ $b->typeLabel() }} @if ($b->gstin)· {{ $b->gstin }}@endif @if ($b->state)· {{ $b->state }}@endif</div></div></div>
            @empty
                <p class="small text-muted p-3 mb-0">No businesses added.</p>
            @endforelse
        </div>
        <div class="card">
            <div class="card-header">Support tickets</div>
            @forelse ($customer->tickets as $t)
                <a href="{{ route('admin.support.show', $t) }}" class="list-row"><div class="min-w-0 flex-grow-1"><div class="title text-truncate">{{ $t->subject }}</div><div class="meta">{{ $t->ticket_no }}</div></div><x-status-badge :status="$t->status" /></a>
            @empty
                <p class="small text-muted p-3 mb-0">No tickets.</p>
            @endforelse
        </div>
    </div>

    <div class="col-xl-8">
        <div class="table-card mb-4">
            <div class="card-header bg-white">Applications</div>
            <div class="table-responsive">
                <table class="table table-hover table-stack">
                    <thead><tr><th>Application</th><th>Service</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse ($customer->applications as $a)
                        <tr>
                            <td data-label="Application"><a href="{{ route('admin.applications.show', $a) }}" class="fw-semibold">{{ $a->application_no }}</a></td>
                            <td data-label="Service">{{ $a->service->name }}</td>
                            <td data-label="Date">{{ $a->created_at->format('d M Y') }}</td>
                            <td data-label="Total">{{ money($a->total) }}</td>
                            <td data-label="Payment"><x-status-badge :status="$a->payment_status" /></td>
                            <td data-label="Status"><x-status-badge :status="$a->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted small py-4">No applications.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Engaged services &amp; subscriptions</div>
            @forelse ($customer->customerServices as $sub)
                <div class="list-row flex-wrap">
                    <div class="min-w-0 flex-grow-1">
                        <div class="title">{{ $sub->service->name }} @if ($sub->isRecurring())<span class="badge badge-soft-teal">{{ \App\Models\Service::INTERVALS[$sub->recurring_interval] ?? 'Recurring' }}</span>@endif</div>
                        <div class="meta">{{ $sub->business?->name ?? '—' }} · since {{ $sub->start_date?->format('d M Y') }} @if ($sub->next_due_date)· next due {{ $sub->next_due_date->format('d M Y') }}@endif</div>
                    </div>
                    @can('customers.edit')
                        <form method="POST" action="{{ route('admin.subscriptions.update', $sub) }}" class="d-flex gap-1">
                            @csrf @method('PUT')
                            <select name="status" class="form-select form-select-sm" style="width:auto">@foreach (\App\Models\CustomerService::STATUSES as $v => $l)<option value="{{ $v }}" @selected($sub->status === $v)>{{ $l }}</option>@endforeach</select>
                            <button class="btn btn-sm btn-light" data-no-lock>Save</button>
                        </form>
                    @else
                        <span class="badge badge-soft-{{ $sub->statusColor() }}">{{ ucfirst($sub->status) }}</span>
                    @endcan
                </div>
            @empty
                <p class="small text-muted p-3 mb-0">Services appear here when an application is completed.</p>
            @endforelse
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Upcoming compliance</div>
                    @forelse ($customer->complianceRecords as $r)
                        <div class="list-row"><div class="min-w-0 flex-grow-1"><div class="title">{{ $r->title }}</div><div class="meta">{{ $r->period_label }} · due {{ $r->due_date->format('d M Y') }}</div></div><x-status-badge :status="$r->status" /></div>
                    @empty
                        <p class="small text-muted p-3 mb-0">No pending compliance.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Recent invoices</div>
                    @forelse ($customer->invoices as $inv)
                        <a href="{{ route('admin.invoices.show', $inv) }}" class="list-row"><div class="flex-grow-1"><div class="title">{{ $inv->invoice_no }}</div><div class="meta">{{ $inv->invoice_date->format('d M Y') }}</div></div><strong>{{ money($inv->total, false) }}</strong><x-status-badge :status="$inv->status" /></a>
                    @empty
                        <p class="small text-muted p-3 mb-0">No invoices.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
