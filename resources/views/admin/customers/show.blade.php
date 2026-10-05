@extends('layouts.admin')
@section('title', (string) ($customer->user->name))

@php
    $user = $customer->user;
    $apps = $customer->applications;
    $activeServices = $customer->customerServices->where('status', 'active');
    $pendingCompliance = $customer->complianceRecords->whereNull('completed_at');
    $openTickets = $customer->tickets->filter(fn ($t) => ! in_array($t->status->value ?? $t->status, ['closed', 'resolved'], true));
    $phone = preg_replace('/\D/', '', (string) $user->mobile);
    $address = collect([$customer->address, $customer->city, $customer->state, $customer->pincode])->filter()->implode(', ');

    // Rough progress through the application workflow, for the progress bar.
    $flow = ['new', 'documents_pending', 'under_review', 'documents_verified', 'payment_pending', 'processing', 'in_progress', 'filed', 'completed'];
    $progress = function ($app) use ($flow) {
        $s = $app->status->value;
        if (in_array($s, ['rejected', 'cancelled'], true)) return null;
        $i = array_search($s, $flow, true);
        return $i === false ? 0 : (int) round(($i + 1) / count($flow) * 100);
    };
@endphp

@section('content')
<a href="{{ route('admin.customers.index') }}" class="cx-back"><i class="bi bi-arrow-left"></i> All customers</a>

{{-- ============ PROFILE HEADER ============ --}}
<div class="cx-hero">
    <div class="cx-hero-main">
        <span class="cx-avatar">{{ $user->initials() }}</span>
        <div class="min-w-0">
            <div class="cx-name">
                <h1>{{ $user->name }}</h1>
                <span class="badge badge-soft-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span>
            </div>
            <div class="cx-meta">
                <span><i class="bi bi-person-vcard"></i> {{ $customer->customer_code }}</span>
                <span><i class="bi bi-calendar3"></i> Customer since {{ $customer->created_at->format('d M Y') }}</span>
                <span><i class="bi bi-clock-history"></i> Last login {{ $user->last_login_at?->diffForHumans() ?? 'never' }}</span>
                <span><i class="bi bi-signpost"></i> Source: {{ ucfirst(str_replace('_', ' ', $customer->source ?? '—')) }}</span>
            </div>
            <div class="cx-contact">
                @if ($user->mobile)<a href="tel:+91{{ $phone }}" class="cx-chip"><i class="bi bi-telephone"></i> +91 {{ $user->mobile }}</a>@endif
                <a href="mailto:{{ $user->email }}" class="cx-chip"><i class="bi bi-envelope"></i> {{ $user->email }}</a>
                @if ($user->mobile)<a href="https://wa.me/91{{ $phone }}" target="_blank" rel="noopener" class="cx-chip wa"><i class="bi bi-whatsapp"></i> WhatsApp</a>@endif
            </div>
        </div>
    </div>
    <div class="cx-actions">
        @can('applications.create')<a href="{{ route('admin.applications.create', ['customer' => $customer->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Application</a>@endcan
        @can('compliance.manage')<a href="{{ route('admin.compliance.create', ['customer' => $customer->id]) }}" class="btn btn-light"><i class="bi bi-calendar-plus me-1"></i>Add Compliance</a>@endcan
        @can('customers.edit')<a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
    </div>
</div>

{{-- ============ KEY NUMBERS ============ --}}
<div class="cx-stats">
    <div class="cx-stat"><span class="ico blue"><i class="bi bi-folder2-open"></i></span><div><strong>{{ $apps->count() }}</strong><small>Applications</small></div></div>
    <div class="cx-stat"><span class="ico teal"><i class="bi bi-briefcase"></i></span><div><strong>{{ $activeServices->count() }}</strong><small>Active services</small></div></div>
    <div class="cx-stat"><span class="ico amber"><i class="bi bi-receipt"></i></span><div><strong>{{ money($totals['billed'], false) }}</strong><small>Total billed</small></div></div>
    <div class="cx-stat"><span class="ico green"><i class="bi bi-check2-circle"></i></span><div><strong>{{ money($totals['paid'], false) }}</strong><small>Total paid</small></div></div>
    <div class="cx-stat"><span class="ico {{ $totals['outstanding'] > 0 ? 'red' : 'green' }}"><i class="bi bi-hourglass-split"></i></span><div><strong>{{ money($totals['outstanding'], false) }}</strong><small>Outstanding</small></div></div>
</div>

{{-- ============ TABS ============ --}}
<ul class="nav cx-tabs" role="tablist">
    <li><button class="active" data-bs-toggle="tab" data-bs-target="#cx-overview" type="button" role="tab"><i class="bi bi-person"></i> Overview</button></li>
    <li><button data-bs-toggle="tab" data-bs-target="#cx-services" type="button" role="tab"><i class="bi bi-folder2-open"></i> Services &amp; applications <span>{{ $apps->count() }}</span></button></li>
    <li><button data-bs-toggle="tab" data-bs-target="#cx-billing" type="button" role="tab"><i class="bi bi-wallet2"></i> Payments &amp; invoices <span>{{ $customer->payments->count() + $customer->invoices->count() }}</span></button></li>
    <li><button data-bs-toggle="tab" data-bs-target="#cx-compliance" type="button" role="tab"><i class="bi bi-calendar-check"></i> Compliance <span>{{ $customer->complianceRecords->count() }}</span></button></li>
    <li><button data-bs-toggle="tab" data-bs-target="#cx-support" type="button" role="tab"><i class="bi bi-headset"></i> Support <span>{{ $customer->tickets->count() }}</span></button></li>
</ul>

<div class="tab-content">
    {{-- ---------- OVERVIEW ---------- --}}
    <div class="tab-pane fade show active" id="cx-overview" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-person-lines-fill me-2 text-muted"></i>Personal &amp; contact details</div>
                    <div class="card-body">
                        <dl class="cx-dl">
                            <dt>Full name</dt><dd>{{ $user->name }}</dd>
                            <dt>Customer ID</dt><dd>{{ $customer->customer_code }}</dd>
                            <dt>Mobile</dt><dd>{{ $user->mobile ? '+91 '.$user->mobile : '—' }}</dd>
                            <dt>Alternate phone</dt><dd>{{ $customer->alt_phone ?: '—' }}</dd>
                            <dt>Email</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd>
                            <dt>PAN</dt><dd>{{ $customer->pan ?: '—' }}</dd>
                            <dt>Address</dt><dd>{{ $address ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-shield-lock me-2 text-muted"></i>Account</div>
                    <div class="card-body">
                        <dl class="cx-dl">
                            <dt>Status</dt><dd><span class="badge badge-soft-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span></dd>
                            <dt>Registered</dt><dd>{{ $customer->created_at->format('d M Y, h:i A') }}</dd>
                            <dt>Source</dt><dd>{{ ucfirst(str_replace('_', ' ', $customer->source ?? '—')) }}</dd>
                            <dt>Last login</dt><dd>{{ $user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</dd>
                            <dt>Email verified</dt><dd>{!! $user->email_verified_at ? '<i class="bi bi-check-circle-fill text-success"></i> Yes' : 'No' !!}</dd>
                            <dt>Open tickets</dt><dd>{{ $openTickets->count() }}</dd>
                            <dt>Pending compliance</dt><dd>{{ $pendingCompliance->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center"><i class="bi bi-buildings me-2 text-muted"></i>Businesses <span class="badge badge-soft-secondary ms-2">{{ $customer->businesses->count() }}</span></div>
                    @if ($customer->businesses->isEmpty())
                        <p class="small text-muted p-3 mb-0">No businesses added.</p>
                    @else
                        <div class="cx-biz-grid">
                            @foreach ($customer->businesses as $b)
                                <div class="cx-biz">
                                    <div class="cx-biz-head">
                                        <span class="cx-biz-ico"><i class="bi bi-building"></i></span>
                                        <div class="min-w-0"><strong>{{ $b->name }}</strong><small>{{ $b->typeLabel() }}</small></div>
                                        @if ($b->is_primary)<span class="badge badge-soft-primary ms-auto">Primary</span>@endif
                                    </div>
                                    <dl class="cx-dl sm">
                                        <dt>GSTIN</dt><dd>{{ $b->gstin ?: '—' }}</dd>
                                        <dt>PAN</dt><dd>{{ $b->pan ?: '—' }}</dd>
                                        <dt>Reg. no.</dt><dd>{{ $b->registration_no ?: '—' }}</dd>
                                        <dt>Incorporated</dt><dd>{{ $b->incorporation_date?->format('d M Y') ?? '—' }}</dd>
                                        <dt>Address</dt><dd>{{ collect([$b->address, $b->city, $b->state, $b->pincode])->filter()->implode(', ') ?: '—' }}</dd>
                                    </dl>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ---------- SERVICES & APPLICATIONS ---------- --}}
    <div class="tab-pane fade" id="cx-services" role="tabpanel">
        <div class="table-card mb-4">
            <div class="cx-card-head"><span><i class="bi bi-folder2-open"></i> All applications</span><small>{{ $apps->count() }} total</small></div>
            @if ($apps->isEmpty())
                <x-empty-state icon="bi-folder2-open" title="No applications yet" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack">
                        <thead><tr><th class="col-sl">#</th><th>Service</th><th>Application</th><th>Business</th><th>Handled by</th><th>Progress</th><th>Amount</th><th>Payment</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($apps as $a)
                            @php($pct = $progress($a))
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Service"><div class="cx-svc"><span class="cx-svc-ico"><i class="bi {{ $a->service->iconClass() }}"></i></span><strong>{{ $a->service->name }}</strong></div></td>
                                <td data-label="Application"><a href="{{ route('admin.applications.show', $a) }}" class="fw-semibold">{{ $a->application_no }}</a><small class="d-block text-muted">{{ $a->created_at->format('d M Y') }}@if ($a->completed_at) · done {{ $a->completed_at->format('d M Y') }}@endif</small></td>
                                <td data-label="Business">{{ $a->business?->name ?? '—' }}</td>
                                <td data-label="Handled by">{{ $a->staff?->name ?? 'Unassigned' }}@if ($a->professional)<small class="d-block text-muted">{{ $a->professional->name }}</small>@endif</td>
                                <td data-label="Progress" style="min-width: 120px">
                                    @if ($pct === null)
                                        <small class="text-muted">—</small>
                                    @else
                                        <div class="cx-progress"><span style="width: {{ $pct }}%" class="{{ $pct === 100 ? 'done' : '' }}"></span></div><small class="text-muted">{{ $pct }}% · {{ $a->documents_count }} docs</small>
                                    @endif
                                </td>
                                <td data-label="Amount"><strong>{{ money($a->total, false) }}</strong>@if ($a->invoice)<small class="d-block"><a href="{{ route('admin.invoices.show', $a->invoice) }}">{{ $a->invoice->invoice_no }}</a></small>@endif</td>
                                <td data-label="Payment"><x-status-badge :status="$a->payment_status" /></td>
                                <td data-label="Status"><x-status-badge :status="$a->status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="cx-card-head"><span><i class="bi bi-briefcase"></i> Engaged services &amp; subscriptions</span><small>{{ $activeServices->count() }} active</small></div>
            @if ($customer->customerServices->isEmpty())
                <p class="small text-muted p-3 mb-0">Services appear here when an application is completed.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack mb-0">
                        <thead><tr><th class="col-sl">#</th><th>Service</th><th>Business</th><th>Billing</th><th>Price</th><th>Started</th><th>Next due</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($customer->customerServices as $sub)
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Service"><div class="cx-svc"><span class="cx-svc-ico teal"><i class="bi {{ $sub->service->iconClass() }}"></i></span><strong>{{ $sub->service->name }}</strong></div></td>
                                <td data-label="Business">{{ $sub->business?->name ?? '—' }}</td>
                                <td data-label="Billing">@if ($sub->isRecurring())<span class="badge badge-soft-teal"><i class="bi bi-arrow-repeat me-1"></i>{{ \App\Models\Service::INTERVALS[$sub->recurring_interval] ?? 'Recurring' }}</span>@else<span class="badge badge-soft-secondary">One-time</span>@endif</td>
                                <td data-label="Price">{{ $sub->price ? money($sub->price, false) : '—' }}</td>
                                <td data-label="Started">{{ $sub->start_date?->format('d M Y') ?? '—' }}</td>
                                <td data-label="Next due">{{ $sub->next_due_date?->format('d M Y') ?? '—' }}</td>
                                <td data-label="Status">
                                    @can('customers.edit')
                                        <form method="POST" action="{{ route('admin.subscriptions.update', $sub) }}" class="d-flex gap-1">
                                            @csrf @method('PUT')
                                            <select name="status" class="form-select form-select-sm" style="width:auto">@foreach (\App\Models\CustomerService::STATUSES as $v => $l)<option value="{{ $v }}" @selected($sub->status === $v)>{{ $l }}</option>@endforeach</select>
                                            <button class="btn btn-sm btn-light" data-no-lock>Save</button>
                                        </form>
                                    @else
                                        <span class="badge badge-soft-{{ $sub->statusColor() }}">{{ ucfirst($sub->status) }}</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ---------- PAYMENTS & INVOICES ---------- --}}
    <div class="tab-pane fade" id="cx-billing" role="tabpanel">
        <div class="table-card mb-4">
            <div class="cx-card-head"><span><i class="bi bi-credit-card"></i> Payments</span><small>{{ money($totals['paid'], false) }} received</small></div>
            @if ($customer->payments->isEmpty())
                <x-empty-state icon="bi-credit-card" title="No payments yet" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack mb-0">
                        <thead><tr><th class="col-sl">#</th><th>Payment</th><th>Application</th><th>Method</th><th>Reference</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($customer->payments as $p)
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Payment" class="fw-semibold">{{ $p->payment_no }}</td>
                                <td data-label="Application">@if ($p->application)<a href="{{ route('admin.applications.show', $p->application) }}">{{ $p->application->application_no }}</a>@else — @endif</td>
                                <td data-label="Method">{{ \App\Models\Payment::METHODS[$p->method] ?? ucfirst((string) $p->method) }}</td>
                                <td data-label="Reference"><small class="text-muted">{{ $p->transaction_id ?: '—' }}</small></td>
                                <td data-label="Date">{{ ($p->paid_at ?? $p->created_at)->format('d M Y') }}</td>
                                <td data-label="Amount"><strong>{{ money($p->amount, false) }}</strong></td>
                                <td data-label="Status"><x-status-badge :status="$p->status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="table-card">
            <div class="cx-card-head"><span><i class="bi bi-receipt"></i> Invoices</span><small>{{ money($totals['billed'], false) }} billed</small></div>
            @if ($customer->invoices->isEmpty())
                <x-empty-state icon="bi-receipt" title="No invoices yet" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack mb-0">
                        <thead><tr><th class="col-sl">#</th><th>Invoice</th><th>Date</th><th>Taxable</th><th>GST</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($customer->invoices as $inv)
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Invoice"><a href="{{ route('admin.invoices.show', $inv) }}" class="fw-semibold">{{ $inv->invoice_no }}</a></td>
                                <td data-label="Date">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td data-label="Taxable">{{ money($inv->subtotal - $inv->discount, false) }}</td>
                                <td data-label="GST">{{ money($inv->cgst + $inv->sgst + $inv->igst, false) }}</td>
                                <td data-label="Total"><strong>{{ money($inv->total, false) }}</strong></td>
                                <td data-label="Status"><x-status-badge :status="$inv->status" /></td>
                                <td class="text-end"><a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ---------- COMPLIANCE ---------- --}}
    <div class="tab-pane fade" id="cx-compliance" role="tabpanel">
        <div class="table-card">
            <div class="cx-card-head"><span><i class="bi bi-calendar-check"></i> Compliance calendar</span><small>{{ $pendingCompliance->count() }} pending</small></div>
            @if ($customer->complianceRecords->isEmpty())
                <x-empty-state icon="bi-calendar-check" title="No compliance records" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack mb-0">
                        <thead><tr><th class="col-sl">#</th><th>Compliance</th><th>Period</th><th>Due date</th><th>Completed</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($customer->complianceRecords as $r)
                            @php($overdue = ! $r->completed_at && $r->due_date->isPast())
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Compliance" class="fw-semibold">{{ $r->title }}</td>
                                <td data-label="Period">{{ $r->period_label ?: '—' }}</td>
                                <td data-label="Due date" class="{{ $overdue ? 'text-danger fw-semibold' : '' }}">{{ $r->due_date->format('d M Y') }}@if ($overdue)<small class="d-block">Overdue</small>@endif</td>
                                <td data-label="Completed">{{ $r->completed_at?->format('d M Y') ?? '—' }}</td>
                                <td data-label="Status"><x-status-badge :status="$r->status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ---------- SUPPORT ---------- --}}
    <div class="tab-pane fade" id="cx-support" role="tabpanel">
        <div class="table-card">
            <div class="cx-card-head"><span><i class="bi bi-headset"></i> Support tickets</span><small>{{ $openTickets->count() }} open</small></div>
            @if ($customer->tickets->isEmpty())
                <x-empty-state icon="bi-headset" title="No support tickets" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-stack mb-0">
                        <thead><tr><th class="col-sl">#</th><th>Ticket</th><th>Subject</th><th>Created</th><th>Last activity</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($customer->tickets as $t)
                            <tr>
                                <td class="col-sl" data-label="#">{{ $loop->iteration }}</td>
                                <td data-label="Ticket"><a href="{{ route('admin.support.show', $t) }}" class="fw-semibold">{{ $t->ticket_no }}</a></td>
                                <td data-label="Subject">{{ $t->subject }}</td>
                                <td data-label="Created">{{ $t->created_at->format('d M Y') }}</td>
                                <td data-label="Last activity">{{ ($t->last_reply_at ?? $t->updated_at)?->diffForHumans() }}</td>
                                <td data-label="Status"><x-status-badge :status="$t->status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Open the tab named in the URL hash (e.g. #cx-billing) and keep the hash in sync.
    (function () {
        var hash = location.hash;
        if (hash) {
            var btn = document.querySelector('.cx-tabs [data-bs-target="' + hash + '"]');
            if (btn && window.bootstrap) bootstrap.Tab.getOrCreateInstance(btn).show();
        }
        document.querySelectorAll('.cx-tabs [data-bs-toggle="tab"]').forEach(function (b) {
            b.addEventListener('shown.bs.tab', function () { history.replaceState(null, '', b.dataset.bsTarget); });
        });
    })();
</script>
@endpush
