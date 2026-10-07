@extends('layouts.admin')
@section('title', (string) ($application->application_no))

@php
    $uploads = $application->documents->where('type', 'customer');
    $deliverables = $application->documents->where('type', 'deliverable');
    $canUpdate = auth()->user()->can('update', $application);
    $openRequests = $application->documentRequests->whereNull('fulfilled_at');
@endphp

@section('content')
<x-page-header :title="$application->application_no.' · '.$application->service->name" :back="route('admin.applications.index')">
    @if ($application->invoice)
        <a href="{{ route('admin.invoices.pdf', $application->invoice) }}" target="_blank" class="btn btn-light"><i class="bi bi-file-earmark-pdf me-1"></i>Invoice</a>
    @endif
    @can('tasks.manage')<a href="{{ route('admin.tasks.create', ['application' => $application->application_no]) }}" class="btn btn-light"><i class="bi bi-check2-square me-1"></i>Add Task</a>@endcan
    @can('delete', $application)
        <form method="POST" action="{{ route('admin.applications.destroy', $application) }}" data-confirm="Delete this application?">@csrf @method('DELETE')<button class="btn btn-light text-danger"><i class="bi bi-trash"></i></button></form>
    @endcan
</x-page-header>

<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <x-status-badge :status="$application->status" class="fs-6" />
    <x-status-badge :status="$application->payment_status" :label="'Payment: '.$application->payment_status->label()" />
    <span class="small text-muted ms-2">Submitted {{ $application->submitted_at?->format('d M Y, h:i A') }}</span>
    @if ($openRequests->isNotEmpty())<span class="badge badge-soft-warning">{{ $openRequests->count() }} document request(s) open</span>@endif
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button">Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button">Documents <span class="badge badge-soft-secondary">{{ $application->documents->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notes" type="button">Notes <span class="badge badge-soft-secondary">{{ $application->notes->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button">Payments</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tasks" type="button">Tasks <span class="badge badge-soft-secondary">{{ $application->tasks->count() }}</span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history" type="button">History</button></li>
        </ul>

        <div class="tab-content">
            {{-- Overview --}}
            <div class="tab-pane fade show active" id="tab-overview">
                <div class="card mb-4">
                    <div class="card-header">Application details</div>
                    <div class="card-body">
                        <dl class="dl-grid">
                            <dt>Service</dt><dd>{{ $application->service->name }} @if ($application->service->isRecurring())<span class="badge badge-soft-teal">{{ $application->service->intervalLabel() }}</span>@endif</dd>
                            <dt>Business</dt><dd>{{ $application->business?->name ?? '—' }} @if ($application->business?->gstin)<small class="text-muted">· GSTIN {{ $application->business->gstin }}</small>@endif</dd>
                            <dt>Amount</dt><dd>{{ money($application->amount) }} @if ((float) $application->discount > 0)<small class="text-green">(− {{ money($application->discount) }} discount)</small>@endif</dd>
                            <dt>GST</dt><dd>{{ money($application->tax) }}</dd>
                            <dt>Total</dt><dd class="fw-bold">{{ money($application->total) }}</dd>
                            @if ($application->completed_at)<dt>Completed</dt><dd>{{ $application->completed_at->format('d M Y') }}</dd>@endif
                        </dl>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Information submitted by customer</div>
                    <div class="card-body">
                        @if (collect($application->form_data)->filter()->isEmpty())
                            <p class="text-muted small mb-0">No additional information.</p>
                        @else
                            <dl class="dl-grid">
                                @foreach ($application->form_data as $key => $value)
                                    @continue($value === null || $value === '')
                                    <dt>{{ $application->service->fields->firstWhere('name', $key)?->label ?? ucwords(str_replace('_', ' ', $key)) }}</dt>
                                    <dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="tab-pane fade" id="tab-documents">
                <div class="card mb-4">
                    <div class="card-header">Customer documents</div>
                    <div class="card-body">
                        @forelse ($uploads as $doc)
                            <div class="doc-row flex-wrap">
                                <i class="bi {{ $doc->icon() }}"></i>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-navy small">{{ $doc->name }}</div>
                                    <div class="small text-muted text-truncate">{{ $doc->original_name }} · {{ $doc->humanSize() }} · {{ $doc->created_at->format('d M Y') }}</div>
                                    @if ($doc->rejection_reason)<div class="small text-danger">Reason: {{ $doc->rejection_reason }}</div>@endif
                                    @if ($doc->reviewer)<div class="small text-muted">Reviewed by {{ $doc->reviewer->name }} · {{ $doc->reviewed_at?->format('d M Y') }}</div>@endif
                                </div>
                                <x-status-badge :status="$doc->status" />
                                <div class="btn-group">
                                    <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.documents.download', $doc) }}" class="btn btn-sm btn-light" title="Download"><i class="bi bi-download"></i></a>
                                </div>
                                @can('review', $doc)
                                    @if (! in_array($doc->status, [\App\Enums\DocumentStatus::Verified, \App\Enums\DocumentStatus::Rejected], true))
                                        <form method="POST" action="{{ route('admin.documents.verify', $doc) }}">@csrf<button class="btn btn-sm btn-cta"><i class="bi bi-check-lg"></i> Verify</button></form>
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#rej{{ $doc->id }}">Reject</button>
                                        <form method="POST" action="{{ route('admin.documents.reject', $doc) }}" class="collapse w-100 mt-2" id="rej{{ $doc->id }}">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="reason" class="form-control" placeholder="Reason (shown to customer)" required maxlength="255">
                                                <select name="reupload" class="form-select" style="max-width:190px"><option value="1">Request re-upload</option><option value="0">Reject only</option></select>
                                                <button class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @empty
                            <p class="text-muted small">The customer has not uploaded any documents yet.</p>
                        @endforelse

                        @php($uploadedIds = $uploads->pluck('service_document_id')->filter()->all())
                        @php($missing = $application->service->documents->reject(fn ($d) => in_array($d->id, $uploadedIds, true)))
                        @if ($missing->isNotEmpty())
                            <div class="alert alert-warning small mt-3 mb-0"><strong>Not yet uploaded:</strong> {{ $missing->pluck('name')->implode(', ') }}</div>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">Request a document</div>
                            <div class="card-body">
                                @foreach ($application->documentRequests as $req)
                                    <div class="small mb-2 d-flex gap-2">
                                        <i class="bi {{ $req->fulfilled_at ? 'bi-check-circle-fill text-green' : 'bi-hourglass-split text-warning' }}"></i>
                                        <span><strong>{{ $req->document_name }}</strong> — {{ $req->fulfilled_at ? 'uploaded '.$req->fulfilled_at->format('d M') : 'requested '.$req->created_at->format('d M').' by '.$req->requester?->name }}</span>
                                    </div>
                                @endforeach
                                @if ($canUpdate)
                                    <form method="POST" action="{{ route('admin.applications.document-requests.store', $application) }}" class="mt-3">
                                        @csrf
                                        <input type="text" name="document_name" class="form-control form-control-sm mb-2" placeholder="Document name" required maxlength="150">
                                        <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Note for the customer (optional)"></textarea>
                                        <button class="btn btn-sm btn-primary">Request &amp; notify customer</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">Final documents / certificates</div>
                            <div class="card-body">
                                @foreach ($deliverables as $doc)
                                    <div class="doc-row">
                                        <i class="bi {{ $doc->icon() }}"></i>
                                        <div class="flex-grow-1 min-w-0"><div class="small fw-semibold text-navy text-truncate">{{ $doc->name }}</div><div class="small text-muted">{{ $doc->uploader?->name }} · {{ $doc->created_at->format('d M Y') }}</div></div>
                                        <a href="{{ route('admin.documents.download', $doc) }}" class="btn btn-sm btn-light"><i class="bi bi-download"></i></a>
                                    </div>
                                @endforeach
                                @if ($canUpdate && auth()->user()->can('documents.upload_final'))
                                    <form method="POST" action="{{ route('admin.applications.deliverables.store', $application) }}" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <input type="text" name="name" class="form-control form-control-sm mb-2" placeholder="e.g. Certificate of Incorporation" required maxlength="150">
                                        <input type="file" name="file" class="form-control form-control-sm mb-2" required accept="{{ \App\Support\FileTypes::accept(\App\Support\FileTypes::DOCUMENTS) }}">
                                        <button class="btn btn-sm btn-cta"><i class="bi bi-upload me-1"></i>Upload &amp; share</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="tab-pane fade" id="tab-notes">
                @if ($canUpdate)
                    <form method="POST" action="{{ route('admin.applications.notes.store', $application) }}" class="card mb-3">
                        @csrf
                        <div class="card-body">
                            <textarea name="note" class="form-control mb-2" rows="3" placeholder="Add a note…" required maxlength="3000"></textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check"><input type="checkbox" class="form-check-input" name="visible_to_customer" value="1" id="vtc"><label for="vtc" class="form-check-label small">Visible to customer</label></div>
                                <button class="btn btn-sm btn-primary">Add Note</button>
                            </div>
                        </div>
                    </form>
                @endif
                @forelse ($application->notes as $note)
                    <div class="note-item {{ $note->is_internal ? 'internal' : '' }}">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><strong class="text-navy">{{ $note->user?->name }}</strong> @if ($note->is_internal)<span class="badge badge-soft-warning">Internal</span>@else<span class="badge badge-soft-info">Shared with customer</span>@endif</span>
                            <span class="text-muted">{{ $note->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        {!! nl2br(e($note->note)) !!}
                    </div>
                @empty
                    <div class="card"><x-empty-state icon="bi-sticky" title="No notes yet" /></div>
                @endforelse
            </div>

            {{-- Payments --}}
            <div class="tab-pane fade" id="tab-payments">
                <div class="table-card mb-3">
                    <div class="table-responsive">
                        <table class="table table-stack">
                            <thead><tr><th>Payment</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @forelse ($application->payments as $payment)
                                <tr>
                                    <td data-label="Payment">{{ $payment->payment_no }}<br><small class="text-muted">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</small></td>
                                    <td data-label="Method">{{ $payment->methodLabel() }}</td>
                                    <td data-label="Reference"><code>{{ $payment->transaction_id ?? '—' }}</code></td>
                                    <td data-label="Amount">{{ money($payment->amount) }}</td>
                                    <td data-label="Status"><x-status-badge :status="$payment->status" /></td>
                                    <td class="td-actions text-end">
                                        @if ($payment->status === \App\Enums\PaymentStatus::Pending)
                                            @can('payments.manage')
                                                <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="d-inline">@csrf<button class="btn btn-sm btn-cta">Confirm</button></form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted small py-4">No payments recorded.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (! $application->isPaid() && (float) $application->total > 0)
                    @can('payments.manage')
                        <form method="POST" action="{{ route('admin.applications.payments.store', $application) }}" class="card">
                            @csrf
                            <div class="card-header">Record offline payment</div>
                            <div class="card-body row g-2">
                                <div class="col-md-3"><input type="number" step="0.01" name="amount" class="form-control" value="{{ $application->total }}" required></div>
                                <div class="col-md-4"><select name="method" class="form-select">@foreach ($paymentMethods as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                                <div class="col-md-3"><input type="text" name="reference" class="form-control" placeholder="UTR / cheque no."></div>
                                <div class="col-md-2 d-grid"><button class="btn btn-primary">Record</button></div>
                            </div>
                        </form>
                    @endcan
                @endif
            </div>

            {{-- Tasks --}}
            <div class="tab-pane fade" id="tab-tasks">
                <div class="card">
                    @forelse ($application->tasks as $task)
                        <div class="list-row">
                            <i class="bi {{ $task->status === \App\Enums\TaskStatus::Completed ? 'bi-check-circle-fill text-green' : 'bi-circle text-muted' }}"></i>
                            <div class="flex-grow-1 min-w-0"><div class="title">{{ $task->title }}</div><div class="meta">{{ $task->assignee?->name ?? 'Unassigned' }} · {{ $task->due_date?->format('d M Y') ?? 'No due date' }}</div></div>
                            <x-status-badge :status="$task->status" />
                        </div>
                    @empty
                        <x-empty-state icon="bi-check2-square" title="No tasks for this application" />
                    @endforelse
                    @can('tasks.manage')<div class="p-3 border-top"><a href="{{ route('admin.tasks.create', ['application' => $application->application_no]) }}" class="btn btn-sm btn-primary">+ Add Task</a></div>@endcan
                </div>
            </div>

            {{-- History --}}
            <div class="tab-pane fade" id="tab-history">
                <div class="card"><div class="card-body">
                    <ul class="progress-timeline">
                        @foreach ($application->histories->reverse() as $h)
                            <li class="done">
                                <span class="dot"><i class="bi bi-arrow-right-short"></i></span>
                                <div class="t-title">{{ $h->from_status?->label() ?? 'Created' }} → {{ $h->to_status->label() }}</div>
                                <div class="t-meta">{{ $h->created_at?->format('d M Y, h:i A') }} · {{ $h->user?->name ?? 'System' }}@if ($h->remarks) — {{ $h->remarks }}@endif</div>
                            </li>
                        @endforeach
                    </ul>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Side panel --}}
    <div class="col-xl-4">
        @if ($canUpdate)
            <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="card mb-4">
                @csrf
                <div class="card-header">Update status</div>
                <div class="card-body">
                    <select name="status" class="form-select mb-2">
                        @foreach ($statuses as $v => $l)<option value="{{ $v }}" @selected($application->status->value === $v)>{{ $l }}</option>@endforeach
                    </select>
                    <textarea name="remarks" class="form-control mb-2" rows="2" placeholder="Remarks (shown to customer)" maxlength="500"></textarea>
                    <div class="form-check small mb-1"><input type="hidden" name="notify" value="0"><input type="checkbox" class="form-check-input" name="notify" value="1" id="notify" checked><label class="form-check-label" for="notify">Notify customer</label></div>
                    @if (! $application->isPaid() && (float) $application->total > 0)
                        <div class="form-check small mb-2"><input type="checkbox" class="form-check-input" name="force" value="1" id="force"><label class="form-check-label" for="force">Complete anyway (payment pending)</label></div>
                    @endif
                    <button class="btn btn-primary w-100">Update Status</button>
                </div>
            </form>
        @endif

        <div class="card mb-4">
            <div class="card-header">Assignment</div>
            <div class="card-body">
                @can('assign', $application)
                    <form method="POST" action="{{ route('admin.applications.assign', $application) }}">
                        @csrf
                        <label class="form-label small">Relationship manager (staff)</label>
                        <select name="assigned_staff_id" class="form-select form-select-sm mb-2"><option value="">— Unassigned —</option>@foreach ($staff as $id => $name)<option value="{{ $id }}" @selected($application->assigned_staff_id === $id)>{{ $name }}</option>@endforeach</select>
                        <label class="form-label small">Professional</label>
                        <select name="assigned_professional_id" class="form-select form-select-sm mb-2"><option value="">— None —</option>@foreach ($professionals as $id => $name)<option value="{{ $id }}" @selected($application->assigned_professional_id === $id)>{{ $name }}</option>@endforeach</select>
                        <button class="btn btn-sm btn-outline-primary w-100">Save Assignment</button>
                    </form>
                @else
                    <dl class="dl-grid"><dt>Staff</dt><dd>{{ $application->staff?->name ?? '—' }}</dd><dt>Professional</dt><dd>{{ $application->professional?->name ?? '—' }}</dd></dl>
                @endcan
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Customer</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar">{{ $application->customer->user->initials() }}</span>
                    <div class="min-w-0">
                        <div class="fw-semibold text-navy">{{ $application->customer->user->name }}</div>
                        <div class="small text-muted">{{ $application->customer->customer_code }}</div>
                    </div>
                </div>
                <div class="small d-grid gap-1">
                    <a href="mailto:{{ $application->customer->user->email }}"><i class="bi bi-envelope me-2"></i>{{ $application->customer->user->email }}</a>
                    @if ($application->customer->user->mobile)<a href="tel:{{ $application->customer->user->mobile }}"><i class="bi bi-telephone me-2"></i>{{ $application->customer->user->mobile }}</a>@endif
                </div>
                @can('customers.view')<a href="{{ route('admin.customers.show', $application->customer) }}" class="btn btn-sm btn-light w-100 mt-3">View customer profile</a>@endcan
            </div>
        </div>

        <div class="card">
            <div class="card-header">Customer timeline</div>
            <div class="card-body"><x-application-timeline :application="$application" /></div>
        </div>
    </div>
</div>
@endsection
