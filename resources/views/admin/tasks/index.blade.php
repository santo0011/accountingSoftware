@extends('layouts.admin')
@section('title', (string) ('Tasks'))

@section('content')
<x-page-header title="Tasks" subtitle="Follow-ups and to-dos across applications and leads.">
    @can('tasks.manage')<a href="{{ route('admin.tasks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Task</a>@endcan
</x-page-header>

@can('tasks.view_all')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link {{ $scope === 'mine' ? 'active' : '' }}" href="{{ route('admin.tasks.index', ['scope' => 'mine']) }}">My tasks</a></li>
        <li class="nav-item"><a class="nav-link {{ $scope === 'all' ? 'active' : '' }}" href="{{ route('admin.tasks.index', ['scope' => 'all']) }}">All tasks</a></li>
    </ul>
@endcan

<form class="filter-bar row g-2" method="GET">
    <input type="hidden" name="scope" value="{{ $scope }}">
    <div class="col-6 col-md-3"><select name="status" class="form-select"><option value="">Open tasks</option>@foreach ($statuses as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><select name="priority" class="form-select"><option value="">Any priority</option>@foreach ($priorities as $v => $l)<option value="{{ $v }}" @selected(request('priority') === $v)>{{ $l }}</option>@endforeach</select></div>
    @if ($scope === 'all')
        <div class="col-md-3"><select name="assignee" class="form-select"><option value="">Anyone</option>@foreach ($staff as $id => $n)<option value="{{ $id }}" @selected(request('assignee') == $id)>{{ $n }}</option>@endforeach</select></div>
    @endif
    <div class="col-md-2"><button class="btn btn-primary w-100" data-no-lock>Filter</button></div>
</form>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover table-stack">
            <thead><tr><th></th><th>Task</th><th>Related to</th><th>Assigned to</th><th>Priority</th><th>Due</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($tasks as $task)
                <tr>
                    <td class="td-actions" style="width:40px">
                        @if ($task->status !== \App\Enums\TaskStatus::Completed)
                            <form method="POST" action="{{ route('admin.tasks.complete', $task) }}">@csrf<button class="btn btn-sm btn-light btn-icon" title="Mark complete" data-no-lock><i class="bi bi-check2"></i></button></form>
                        @else
                            <i class="bi bi-check-circle-fill text-green"></i>
                        @endif
                    </td>
                    <td data-label="Task"><span class="fw-semibold text-navy">{{ $task->title }}</span>@if ($task->description)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($task->description, 80) }}</small>@endif</td>
                    <td data-label="Related to">
                        @if ($task->taskable instanceof \App\Models\Application)<a href="{{ route('admin.applications.show', $task->taskable) }}">{{ $task->relatedLabel() }}</a>
                        @elseif ($task->taskable instanceof \App\Models\Lead)<a href="{{ route('admin.leads.show', $task->taskable) }}">{{ $task->relatedLabel() }}</a>
                        @else — @endif
                    </td>
                    <td data-label="Assigned to">{{ $task->assignee?->name ?? '—' }}</td>
                    <td data-label="Priority"><span class="badge badge-soft-{{ $task->priorityColor() }}">{{ ucfirst($task->priority) }}</span></td>
                    <td data-label="Due" class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                    <td data-label="Status"><x-status-badge :status="$task->status" /></td>
                    <td class="td-actions text-end text-nowrap">
                        @can('tasks.manage')
                            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" class="d-inline" data-confirm="Delete this task?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty-state icon="bi-check2-all" title="No tasks" text="Nothing pending here." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer"><span>{{ $tasks->total() }} tasks</span>{{ $tasks->links() }}</div>
</div>
@endsection
