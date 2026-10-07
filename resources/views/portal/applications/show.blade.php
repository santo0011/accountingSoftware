@extends('layouts.portal')
@section('title', (string) ('Application '.$application->application_no))

@php
    $uploads = $application->documents->where('type', 'customer');
    $deliverables = $application->documents->where('type', 'deliverable');
    $canUpload = auth()->user()->can('upload', $application);
@endphp

@section('content')
<x-page-header :title="$application->service->name" :back="route('portal.applications.index')">
    @can('pay', $application)
        <a href="{{ route('portal.payments.checkout', $application) }}" class="btn btn-cta"><i class="bi bi-credit-card me-1"></i>Pay {{ money($application->total) }}</a>
    @endcan
    <a href="{{ route('portal.support.create', ['application' => $application->id]) }}" class="btn btn-outline-primary"><i class="bi bi-headset me-1"></i>Get Help</a>
    @can('cancel', $application)
        <form method="POST" action="{{ route('portal.applications.cancel', $application) }}" data-confirm="Cancel this application? This cannot be undone.">
            @csrf<button class="btn btn-light text-danger">Cancel</button>
        </form>
    @endcan
</x-page-header>

<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <span class="fw-semibold text-navy">{{ $application->application_no }}</span>
    <x-status-badge :status="$application->status" />
    <x-status-badge :status="$application->payment_status" :label="'Payment: '.$application->payment_status->label()" />
</div>

@if ($application->documentRequests->isNotEmpty() && $canUpload)
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning-subtle"><i class="bi bi-cloud-upload me-1"></i> Documents requested by our team</div>
        <div class="card-body">
            @foreach ($application->documentRequests as $req)
                <form method="POST" action="{{ route('portal.applications.documents.store', $application) }}" enctype="multipart/form-data" class="upload-box mb-3">
                    @csrf
                    <input type="hidden" name="document_request_id" value="{{ $req->id }}">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-navy">{{ $req->document_name }}</div>
                            @if ($req->note)<div class="small text-muted">{{ $req->note }}</div>@endif
                            <div class="small text-muted" data-file-name data-empty="No file chosen">No file chosen</div>
                        </div>
                        <label class="btn btn-sm btn-outline-primary mb-0">Choose file<input type="file" name="file" class="d-none" required accept="{{ \App\Support\FileTypes::accept(\App\Support\FileTypes::DOCUMENTS) }}"></label>
                        <button class="btn btn-sm btn-cta">Upload</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-8">
        {{-- Documents --}}
        <div class="card mb-4">
            <div class="card-header">Documents</div>
            <div class="card-body">
                @if ($deliverables->isNotEmpty())
                    <div class="small-caps text-green mb-2"><i class="bi bi-patch-check me-1"></i>Final documents</div>
                    @foreach ($deliverables as $doc)
                        <div class="doc-row border-success">
                            <i class="bi {{ $doc->icon() }}"></i>
                            <div class="flex-grow-1 min-w-0"><div class="fw-semibold text-navy small text-truncate">{{ $doc->name }}</div><div class="small text-muted">{{ $doc->humanSize() }} · {{ $doc->created_at->format('d M Y') }}</div></div>
                            <a href="{{ route('portal.documents.download', $doc) }}" class="btn btn-sm btn-cta"><i class="bi bi-download me-1"></i>Download</a>
                        </div>
                    @endforeach
                    <div class="divider"></div>
                @endif

                <div class="small-caps text-muted mb-2">Your uploads</div>
                @forelse ($uploads as $doc)
                    <div class="doc-row flex-wrap">
                        <i class="bi {{ $doc->icon() }}"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-navy small text-truncate">{{ $doc->name }}</div>
                            <div class="small text-muted text-truncate">{{ $doc->original_name }} · {{ $doc->humanSize() }}</div>
                            @if ($doc->rejection_reason && in_array($doc->status, [\App\Enums\DocumentStatus::Rejected, \App\Enums\DocumentStatus::ReuploadRequired], true))
                                <div class="small text-danger mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $doc->rejection_reason }}</div>
                            @endif
                        </div>
                        <x-status-badge :status="$doc->status" />
                        <a href="{{ route('portal.documents.download', $doc) }}" class="btn btn-sm btn-light" title="Download"><i class="bi bi-download"></i></a>
                        @if ($doc->status === \App\Enums\DocumentStatus::ReuploadRequired && $canUpload)
                            <form method="POST" action="{{ route('portal.applications.documents.store', $application) }}" enctype="multipart/form-data" class="w-100 upload-box mt-2 py-2">
                                @csrf
                                <input type="hidden" name="replaces_id" value="{{ $doc->id }}">
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="small text-muted flex-grow-1" data-file-name data-empty="Choose the corrected file">Choose the corrected file</span>
                                    <label class="btn btn-sm btn-outline-primary mb-0">Choose<input type="file" name="file" class="d-none" required></label>
                                    <button class="btn btn-sm btn-cta">Re-upload</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="small text-muted">No documents uploaded yet.</p>
                @endforelse

                @if ($missing->isNotEmpty() && $canUpload)
                    <div class="small-caps text-muted mt-4 mb-2">Still to upload</div>
                    @foreach ($missing as $doc)
                        <form method="POST" action="{{ route('portal.applications.documents.store', $application) }}" enctype="multipart/form-data" class="upload-box mb-2 py-2">
                            @csrf
                            <input type="hidden" name="service_document_id" value="{{ $doc->id }}">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small fw-semibold text-navy">{{ $doc->name }} @if ($doc->is_mandatory)<span class="badge badge-soft-warning">Required</span>@endif</div>
                                    <div class="small text-muted" data-file-name data-empty="No file chosen">No file chosen</div>
                                </div>
                                <label class="btn btn-sm btn-outline-primary mb-0">Choose<input type="file" name="file" class="d-none" required accept="{{ \App\Support\FileTypes::accept(\App\Support\FileTypes::DOCUMENTS) }}"></label>
                                <button class="btn btn-sm btn-cta">Upload</button>
                            </div>
                        </form>
                    @endforeach
                @endif

                @if ($canUpload)
                    <details class="mt-3">
                        <summary class="small fw-semibold text-brand cursor-pointer">Upload another document</summary>
                        <form method="POST" action="{{ route('portal.applications.documents.store', $application) }}" enctype="multipart/form-data" class="row g-2 mt-2">
                            @csrf
                            <div class="col-md-5"><input type="text" name="name" class="form-control form-control-sm" placeholder="Document name" required maxlength="150"></div>
                            <div class="col-md-5"><input type="file" name="file" class="form-control form-control-sm" required></div>
                            <div class="col-md-2 d-grid"><button class="btn btn-sm btn-primary">Upload</button></div>
                        </form>
                    </details>
                @endif
            </div>
        </div>

        {{-- Updates from the team --}}
        <div class="card mb-4">
            <div class="card-header">Updates from our team</div>
            <div class="card-body">
                @forelse ($application->notes as $note)
                    <div class="note-item">
                        <div class="d-flex justify-content-between small mb-1"><strong class="text-navy">{{ $note->user?->name ?? 'Team' }}</strong><span class="text-muted">{{ $note->created_at->format('d M Y, h:i A') }}</span></div>
                        {!! nl2br(e($note->note)) !!}
                    </div>
                @empty
                    <p class="small text-muted mb-0">Updates from your relationship manager will appear here.</p>
                @endforelse
            </div>
        </div>

        {{-- Submitted details --}}
        @if ($application->form_data)
            <div class="card">
                <div class="card-header">Details you submitted</div>
                <div class="card-body">
                    <dl class="dl-grid">
                        @foreach ($application->form_data as $key => $value)
                            @continue($value === null || $value === '')
                            <dt>{{ $application->service->fields->firstWhere('name', $key)?->label ?? ucwords(str_replace('_', ' ', $key)) }}</dt>
                            <dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">Progress</div>
            <div class="card-body"><x-application-timeline :application="$application" /></div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Application details</div>
            <div class="card-body">
                <dl class="dl-grid">
                    <dt>Application no.</dt><dd>{{ $application->application_no }}</dd>
                    <dt>Applied on</dt><dd>{{ $application->created_at->format('d M Y') }}</dd>
                    <dt>Business</dt><dd>{{ $application->business?->name ?? '—' }}</dd>
                    <dt>Relationship manager</dt><dd>{{ $application->staff?->name ?? 'Being assigned' }}</dd>
                    <dt>Professional</dt><dd>{{ $application->professional?->name ?? '—' }}</dd>
                    <dt>Amount</dt><dd>{{ money($application->total) }}</dd>
                    <dt>Payment</dt><dd><x-status-badge :status="$application->payment_status" /></dd>
                    @if ($application->completed_at)<dt>Completed</dt><dd>{{ $application->completed_at->format('d M Y') }}</dd>@endif
                </dl>
                @if ($application->invoice)
                    <a href="{{ route('portal.invoices.pdf', $application->invoice) }}" class="btn btn-outline-primary btn-sm w-100 mt-3"><i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice {{ $application->invoice->invoice_no }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
