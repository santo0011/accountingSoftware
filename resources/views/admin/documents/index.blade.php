@extends('layouts.admin')
@section('title', (string) ('Documents'))

@section('content')
<x-page-header title="Document review" subtitle="Verify or reject documents uploaded by customers." />

<ul class="nav nav-tabs mb-3">
    @foreach (['pending' => 'To review', 'verified' => 'Verified', 'reupload_required' => 'Re-upload requested', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
        <li class="nav-item"><a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.documents.index', ['status' => $key]) }}">{{ $label }}</a></li>
    @endforeach
</ul>

<form class="filter-bar row g-2" method="GET">
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Application number"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Search</button></div>
</form>

<div class="table-card">
    @if ($documents->isEmpty())
        <x-empty-state icon="bi-file-earmark-check" title="No documents here" />
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th>Document</th><th>Application</th><th>Customer</th><th>Uploaded</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($documents as $doc)
                    <tr>
                        <td data-label="Document"><span class="d-inline-flex gap-2 align-items-center"><i class="bi {{ $doc->icon() }} fs-5"></i><span><span class="fw-semibold text-navy d-block">{{ $doc->name }}</span><small class="text-muted">{{ \Illuminate\Support\Str::limit($doc->original_name, 35) }} · {{ $doc->humanSize() }}</small></span></span></td>
                        <td data-label="Application"><a href="{{ route('admin.applications.show', $doc->application) }}#tab-documents">{{ $doc->application->application_no }}</a><br><small class="text-muted">{{ $doc->application->service->name }}</small></td>
                        <td data-label="Customer">{{ $doc->application->customer->user->name }}</td>
                        <td data-label="Uploaded">{{ $doc->created_at->format('d M Y') }}</td>
                        <td data-label="Status"><x-status-badge :status="$doc->status" /></td>
                        <td class="td-actions text-end text-nowrap">
                            <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                            @can('review', $doc)
                                @if (in_array($doc->status, [\App\Enums\DocumentStatus::Pending, \App\Enums\DocumentStatus::UnderReview], true))
                                    <form method="POST" action="{{ route('admin.documents.verify', $doc) }}" class="d-inline">@csrf<button class="btn btn-sm btn-cta" title="Verify"><i class="bi bi-check-lg"></i></button></form>
                                    <a href="{{ route('admin.applications.show', $doc->application) }}#tab-documents" class="btn btn-sm btn-outline-danger" title="Reject with reason"><i class="bi bi-x-lg"></i></a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>{{ $documents->total() }} documents</span>{{ $documents->links() }}</div>
    @endif
</div>
@endsection
