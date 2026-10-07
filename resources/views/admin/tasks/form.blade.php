@extends('layouts.admin')
@section('title', (string) ($task->exists ? 'Edit Task' : 'New Task'))

@php($typeKey = $task->taskable_type ? ([\App\Models\Application::class => 'application', \App\Models\Lead::class => 'lead'][$task->taskable_type] ?? null) : null)

@section('content')
<x-page-header :title="$task->exists ? 'Edit task' : 'New task'" :subtitle="$task->exists ? 'Update the task, owner or due date.' : 'Create a to-do for yourself or a team member.'" :back="route('admin.tasks.index')" />

<form method="POST" action="{{ $task->exists ? route('admin.tasks.update', $task) : route('admin.tasks.store') }}" novalidate>
    @csrf
    @if ($task->exists) @method('PUT') @endif
    <input type="hidden" name="taskable_type" value="{{ old('taskable_type', $typeKey) }}">
    <input type="hidden" name="taskable_id" value="{{ old('taskable_id', $task->taskable_id) }}">

    <div class="row g-4">
        <div class="col-xl-8">
            <x-form.section title="Task" text="What needs to be done." icon="bi-check2-square">
                @if ($task->taskable)
                    <div class="col-12 mb-3"><span class="badge badge-soft-primary"><i class="bi bi-link-45deg"></i> Linked to {{ $task->relatedLabel() }}</span></div>
                @endif
                <x-form.input name="title" label="Title" :value="$task->title" required placeholder="e.g. Call customer about pending PAN copy" col="col-12 mb-3" />
                <x-form.textarea name="description" label="Details" :value="$task->description" rows="4" placeholder="Anything the assignee should know — documents, deadlines, contact person…" col="col-12" />
            </x-form.section>

            <x-form.section title="Assignment & schedule" text="Who does it, by when, and how urgent it is." icon="bi-calendar-event" tone="teal">
                <x-form.select name="assigned_to" label="Assign to" :options="$staff" :value="$task->assigned_to" placeholder="Unassigned" col="col-md-6 mb-3" />
                <div class="col-md-6 mb-3">
                    <x-form.input name="due_date" type="date" label="Due date" :value="$task->due_date?->format('Y-m-d')" col="" />
                    <div class="quick-dates" data-for="f_due_date" role="group" aria-label="Quick due dates">
                        <button type="button" data-days="0">Today</button>
                        <button type="button" data-days="1">Tomorrow</button>
                        <button type="button" data-days="3">In 3 days</button>
                        <button type="button" data-days="7">Next week</button>
                    </div>
                </div>
                <x-form.select name="priority" label="Priority" :options="$priorities" :value="$task->priority" col="col-md-6 mb-md-0 mb-3" />
                <x-form.select name="status" label="Status" :options="$statuses" :value="$task->status" col="col-md-6" />
            </x-form.section>
        </div>

        <div class="col-xl-4">
            <x-form.tips title="Good tasks are…">
                <li><strong>Specific</strong> — start the title with a verb: “Call”, “Upload”, “Verify”.</li>
                <li><strong>Owned</strong> — assign one person; they get a notification.</li>
                <li><strong>Dated</strong> — overdue tasks are highlighted in red on the task list and dashboard.</li>
                <li>Tasks created from an application or lead stay linked to it.</li>
            </x-form.tips>
        </div>
    </div>

    <x-form.actions :cancel="route('admin.tasks.index')" :label="$task->exists ? 'Save changes' : 'Create task'" />
</form>
@endsection
