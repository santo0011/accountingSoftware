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
            <thead><tr><th class="col-sl">#</th><th>When</th><th>User</th><th>Action</th><th>Changes</th></tr></thead>
            <tbody>
            @forelse ($activities as $a)
                <tr>
                    <td class="col-sl" data-label="#">{{ $activities->firstItem() + $loop->index }}</td>
                    <td data-label="When" class="text-nowrap stack-multi">
                        <span class="d-block">{{ $a->created_at->format('d M Y') }}</span>
                        <small class="text-muted d-block">{{ $a->created_at->format('h:i A') }}</small>
                    </td>
                    <td data-label="User" class="text-nowrap stack-multi">
                        <span class="d-block">{{ $a->causer?->name ?? 'System' }}</span>
                        <small class="text-muted d-block">{{ $a->log_name }}</small>
                    </td>
                    <td data-label="Action" class="text-nowrap stack-multi">
                        <span class="d-block">{{ ucfirst($a->description) }}</span>
                        @if ($a->subject_type)<small class="text-muted d-block">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</small>@endif
                    </td>
                    <td data-label="Changes" class="small audit-changes stack-multi">
                        @php($new = $a->properties['attributes'] ?? [])
                        @php($old = $a->properties['old'] ?? [])
                        @foreach (array_slice($new, 0, 5, true) as $key => $value)
                            <div class="text-truncate" title="{{ $key }}: {{ is_scalar($value) ? $value : json_encode($value) }}"><span class="text-muted">{{ $key }}:</span> @if (array_key_exists($key, $old))<del class="text-danger">{{ \Illuminate\Support\Str::limit(is_scalar($old[$key]) ? (string) $old[$key] : json_encode($old[$key]), 30) }}</del> → @endif{{ \Illuminate\Support\Str::limit(is_scalar($value) ? (string) $value : json_encode($value), 40) }}</div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state icon="bi-journal-text" title="No activity" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-footer :items="$activities" label="entries" />
</div>
@endsection
