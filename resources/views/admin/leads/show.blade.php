@extends('layouts.admin')
@section('title', (string) ('Lead: '.$lead->name))

@section('content')
<x-page-header :title="$lead->name" :subtitle="collect([$lead->company, $lead->sourceLabel(), 'added '.$lead->created_at->format('d M Y')])->filter()->implode(' · ')" :back="route('admin.leads.index')">
    @if (! $lead->customer_id)
        @can('leads.edit')
            <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" data-confirm="Create a customer account for {{ $lead->name }} and email them a set-password link?">@csrf
                <button class="btn btn-cta"><i class="bi bi-person-check me-1"></i>Convert to Customer</button>
            </form>
        @endcan
    @else
        <a href="{{ route('admin.customers.show', $lead->customer_id) }}" class="btn btn-outline-primary"><i class="bi bi-person me-1"></i>View Customer</a>
    @endif
    @can('leads.edit')<a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
    @can('leads.delete')
        <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" data-confirm="Delete this lead?">@csrf @method('DELETE')<button class="btn btn-light text-danger"><i class="bi bi-trash"></i></button></form>
    @endcan
</x-page-header>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3"><x-status-badge :status="$lead->status" class="fs-6" /></div>
                <dl class="dl-grid">
                    <dt>Phone</dt><dd>@if ($lead->phone)<a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a> · <a href="https://wa.me/91{{ $lead->phone }}" target="_blank"><i class="bi bi-whatsapp text-success"></i></a>@else — @endif</dd>
                    <dt>Email</dt><dd>@if ($lead->email)<a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@else — @endif</dd>
                    <dt>City</dt><dd>{{ $lead->city ?? '—' }}</dd>
                    <dt>Service</dt><dd>{{ $lead->service?->name ?? '—' }}</dd>
                    <dt>Assigned to</dt><dd>{{ $lead->assignee?->name ?? 'Unassigned' }}</dd>
                    <dt>Next follow-up</dt><dd>{{ $lead->next_followup_at?->format('d M Y') ?? '—' }}</dd>
                </dl>
                @if ($lead->message)<div class="divider"></div><div class="small-caps text-muted mb-1">Enquiry</div><p class="small mb-0">{{ $lead->message }}</p>@endif
                @if ($lead->notes)<div class="divider"></div><div class="small-caps text-muted mb-1">Notes</div><p class="small mb-0">{!! nl2br(e($lead->notes)) !!}</p>@endif
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">Tasks @can('tasks.manage')<a href="{{ route('admin.tasks.create', ['lead' => $lead->id]) }}" class="small">+ Add</a>@endcan</div>
            @forelse ($lead->tasks as $task)
                <div class="list-row"><div class="flex-grow-1 min-w-0"><div class="title">{{ $task->title }}</div><div class="meta">{{ $task->assignee?->name }} · {{ $task->due_date?->format('d M Y') }}</div></div><x-status-badge :status="$task->status" /></div>
            @empty
                <p class="small text-muted p-3 mb-0">No tasks.</p>
            @endforelse
        </div>
    </div>

    <div class="col-xl-8">
        @can('leads.edit')
            <form method="POST" action="{{ route('admin.leads.followups.store', $lead) }}" class="card mb-4">
                @csrf
                <div class="card-header">Log a follow-up</div>
                <div class="card-body row g-2">
                    <div class="col-md-4"><select name="channel" class="form-select">@foreach (\App\Models\LeadFollowup::CHANNELS as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-4"><select name="status" class="form-select"><option value="">Keep status</option>@foreach ($statuses as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-4"><input type="date" name="next_followup_at" class="form-control" min="{{ now()->format('Y-m-d') }}" title="Next follow-up (creates a task)"></div>
                    <div class="col-12"><textarea name="remarks" class="form-control" rows="2" placeholder="What was discussed?" required></textarea></div>
                    <div class="col-12 text-end"><button class="btn btn-primary btn-sm">Save Follow-up</button></div>
                </div>
            </form>
        @endcan
        <div class="card">
            <div class="card-header">Activity</div>
            <div class="card-body">
                @if ($lead->followups->isEmpty())
                    <p class="text-muted small mb-0">No follow-ups logged yet.</p>
                @else
                    <ul class="progress-timeline">
                        @foreach ($lead->followups as $f)
                            <li class="done">
                                <span class="dot"><i class="bi {{ ['call' => 'bi-telephone', 'email' => 'bi-envelope', 'whatsapp' => 'bi-whatsapp', 'meeting' => 'bi-people'][$f->channel] ?? 'bi-chat' }}"></i></span>
                                <div class="t-title">{{ \App\Models\LeadFollowup::CHANNELS[$f->channel] ?? $f->channel }} · {{ $f->user?->name }}</div>
                                <div class="small">{{ $f->remarks }}</div>
                                <div class="t-meta">{{ $f->created_at->format('d M Y, h:i A') }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
