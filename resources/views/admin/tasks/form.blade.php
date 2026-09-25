@extends('layouts.admin')
@section('title', (string) ($task->exists ? 'Edit Task' : 'New Task'))

@php($typeKey = $task->taskable_type ? ([\App\Models\Application::class => 'application', \App\Models\Lead::class => 'lead'][$task->taskable_type] ?? null) : null)

@section('content')
<x-page-header :title="$task->exists ? 'Edit task' : 'New task'" :back="route('admin.tasks.index')" />

<form method="POST" action="{{ $task->exists ? route('admin.tasks.update', $task) : route('admin.tasks.store') }}" class="card" style="max-width: 800px" novalidate>
    @csrf
    @if ($task->exists) @method('PUT') @endif
    <input type="hidden" name="taskable_type" value="{{ old('taskable_type', $typeKey) }}">
    <input type="hidden" name="taskable_id" value="{{ old('taskable_id', $task->taskable_id) }}">
    <div class="card-body row">
        @if ($task->taskable)
            <div class="col-12 mb-3"><span class="badge badge-soft-primary"><i class="bi bi-link-45deg"></i> {{ $task->relatedLabel() }}</span></div>
        @endif
        <x-form.input name="title" label="Title" :value="$task->title" required col="col-12 mb-3" />
        <x-form.textarea name="description" label="Details" :value="$task->description" rows="3" col="col-12 mb-3" />
        <x-form.select name="assigned_to" label="Assign to" :options="$staff" :value="$task->assigned_to" placeholder="Unassigned" col="col-md-6 mb-3" />
        <x-form.input name="due_date" type="date" label="Due date" :value="$task->due_date?->format('Y-m-d')" col="col-md-6 mb-3" />
        <x-form.select name="priority" label="Priority" :options="$priorities" :value="$task->priority" col="col-md-6 mb-3" />
        <x-form.select name="status" label="Status" :options="$statuses" :value="$task->status" col="col-md-6 mb-3" />
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Task</button></div>
</form>
@endsection
