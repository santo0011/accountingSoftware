@extends('layouts.admin')
@section('title', (string) ('Audit Log'))

@section('content')
<x-page-header title="Audit log" subtitle="Who changed what, and when." />

<form class="filter-bar row g-2" method="GET">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search description"></div>
    <div class="col-6 col-md-2"><select name="log" class="form-select"><option value="">All areas</option>@foreach ($logs as $l)<option value="{{ $l }}" @selected(request('log') === $l)>{{ ucfirst($l) }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
    <div class="col-6 col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
    <div class="col-6 col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-stack">
            <thead><tr><th>When</th><th>User</th><th>Area</th><th>Action</th><th>Changes</th></tr></thead>
            <tbody>
            @forelse ($activities as $a)
                <tr>
                    <td data-label="When" class="text-nowrap">{{ $a->created_at->format('d M Y, h:i A') }}</td>
                    <td data-label="User">{{ $a->causer?->name ?? 'System' }}</td>
                    <td data-label="Area"><span class="badge badge-soft-secondary">{{ $a->log_name }}</span></td>
                    <td data-label="Action">{{ ucfirst($a->description) }} @if ($a->subject_type)<small class="text-muted">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</small>@endif</td>
                    <td data-label="Changes" class="small">
                        @php($new = $a->properties['attributes'] ?? [])
                        @php($old = $a->properties['old'] ?? [])
                        @foreach (array_slice($new, 0, 5, true) as $key => $value)
                            <div><span class="text-muted">{{ $key }}:</span> @if (array_key_exists($key, $old))<del class="text-danger">{{ \Illuminate\Support\Str::limit(is_scalar($old[$key]) ? (string) $old[$key] : json_encode($old[$key]), 30) }}</del> → @endif{{ \Illuminate\Support\Str::limit(is_scalar($value) ? (string) $value : json_encode($value), 40) }}</div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state icon="bi-journal-text" title="No activity" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $activities->total() }} entries</span>{{ $activities->links() }}</div>
</div>
@endsection
