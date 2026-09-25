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
                <thead><tr><th>Application</th><th>Service</th><th>Date</th><th>Amount</th><th>Payment</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td data-label="Application"><a href="{{ route('portal.applications.show', $app) }}" class="fw-semibold">{{ $app->application_no }}</a></td>
                        <td data-label="Service"><span class="d-inline-flex align-items-center gap-2"><i class="bi {{ $app->service->iconClass() }} text-brand"></i>{{ $app->service->name }}</span></td>
                        <td data-label="Date">{{ $app->created_at->format('d M Y') }}</td>
                        <td data-label="Amount">{{ money($app->total) }}</td>
                        <td data-label="Payment"><x-status-badge :status="$app->payment_status" /></td>
                        <td data-label="Status"><x-status-badge :status="$app->status" /></td>
                        <td class="td-actions text-end">
                            @can('pay', $app)<a href="{{ route('portal.payments.checkout', $app) }}" class="btn btn-sm btn-cta">Pay</a>@endcan
                            <a href="{{ route('portal.applications.show', $app) }}" class="btn btn-sm btn-light">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }}</span>{{ $applications->links() }}</div>
    @endif
</div>
@endsection
