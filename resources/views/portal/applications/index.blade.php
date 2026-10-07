@extends('layouts.portal')
@section('title', (string) ('My Applications'))

@section('content')
<x-page-header title="Applications" subtitle="Track every service you have applied for.">
    <a href="{{ route('site.services.index') }}" class="btn btn-cta"><i class="bi bi-plus-lg me-1"></i>New Application</a>
</x-page-header>

<ul class="nav nav-tabs mb-3">
    @foreach (['all' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'closed' => 'Cancelled / Rejected'] as $key => $label)
        <li class="nav-item"><a class="nav-link {{ $filter === $key ? 'active' : '' }}" href="{{ route('portal.applications.index', ['filter' => $key]) }}">{{ $label }}</a></li>
    @endforeach
</ul>

<div class="table-card">
    @if ($applications->isEmpty())
        <x-empty-state icon="bi-folder2-open" title="No applications found">
            <a href="{{ route('site.services.index') }}" class="btn btn-sm btn-cta">Apply for a Service</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-stack">
                <thead><tr><th class="col-sl">#</th><th>Application</th><th>Service</th><th class="text-end">Amount &amp; payment</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td class="col-sl" data-label="#">{{ $applications->firstItem() + $loop->index }}</td>
                        <td data-label="Application" class="text-nowrap stack-multi">
                            <a href="{{ route('portal.applications.show', $app) }}" class="fw-semibold d-block">{{ $app->application_no }}</a>
                            <small class="text-muted d-block">{{ $app->created_at->format('d M Y') }}</small>
                        </td>
                        <td data-label="Service" class="td-clip wide" title="{{ $app->service->name }}"><span class="d-block"><i class="bi {{ $app->service->iconClass() }} text-brand me-2"></i>{{ $app->service->name }}</span></td>
                        <td data-label="Amount &amp; payment" class="text-end text-nowrap stack-multi">
                            <span class="fw-semibold d-block">{{ money($app->total) }}</span>
                            <span class="d-block mt-1"><x-status-badge :status="$app->payment_status" /></span>
                        </td>
                        <td data-label="Status" class="text-nowrap"><x-status-badge :status="$app->status" /></td>
                        <td class="td-actions text-end text-nowrap">
                            @can('pay', $app)<a href="{{ route('portal.payments.checkout', $app) }}" class="btn btn-sm btn-cta">Pay</a>@endcan
                            <a href="{{ route('portal.applications.show', $app) }}" class="btn btn-sm btn-light">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-table-footer :items="$applications" label="applications" />
    @endif
</div>
@endsection
