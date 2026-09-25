@extends('layouts.portal')
@section('title', (string) ('Documents'))

@section('content')
<x-page-header title="Documents" subtitle="All documents you uploaded and the final documents we delivered." />

@if ($attention->isNotEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>{{ $attention->count() }} document(s) need to be re-uploaded:</strong>
        @foreach ($attention as $doc)
            <a href="{{ route('portal.applications.show', $doc->application) }}" class="fw-semibold">{{ $doc->name }} ({{ $doc->application->application_no }})</a>@if (! $loop->last), @endif
        @endforeach
    </div>
@endif

<ul class="nav nav-tabs mb-3">
    @foreach (['' => 'All', 'deliverable' => 'Final documents', 'uploaded' => 'My uploads'] as $key => $label)
        <li class="nav-item"><a class="nav-link {{ request('type', '') === $key ? 'active' : '' }}" href="{{ route('portal.documents.index', array_filter(['type' => $key])) }}">{{ $label }}</a></li>
    @endforeach
</ul>

<div class="table-card">
    @if ($documents->isEmpty())
        <x-empty-state icon="bi-file-earmark" title="No documents yet" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th>Document</th><th>Application</th><th>Type</th><th>Uploaded</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($documents as $doc)
                    <tr>
                        <td data-label="Document"><span class="d-inline-flex align-items-center gap-2"><i class="bi {{ $doc->icon() }} fs-5"></i><span><span class="fw-semibold text-navy d-block">{{ $doc->name }}</span><small class="text-muted">{{ \Illuminate\Support\Str::limit($doc->original_name, 40) }} · {{ $doc->humanSize() }}</small></span></span></td>
                        <td data-label="Application"><a href="{{ route('portal.applications.show', $doc->application) }}">{{ $doc->application->application_no }}</a><br><small class="text-muted">{{ $doc->application->service->name }}</small></td>
                        <td data-label="Type">@if ($doc->isDeliverable())<span class="badge badge-soft-success">Final document</span>@else<span class="badge badge-soft-secondary">Uploaded</span>@endif</td>
                        <td data-label="Uploaded">{{ $doc->created_at->format('d M Y') }}</td>
                        <td data-label="Status"><x-status-badge :status="$doc->status" /></td>
                        <td class="td-actions text-end"><a href="{{ route('portal.documents.download', $doc) }}" class="btn btn-sm btn-light"><i class="bi bi-download me-1"></i>Download</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $documents->total() }} documents</span>{{ $documents->links() }}</div>
    @endif
</div>
@endsection
