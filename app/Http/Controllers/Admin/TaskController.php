<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AssignedToYou;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TaskController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:tasks.view', only: ['index']),
            new Middleware('permission:tasks.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $scope = $user->can('tasks.view_all') ? $request->query('scope', 'mine') : 'mine';

        $tasks = Task::with('assignee:id,name', 'creator:id,name', 'taskable')
            ->when($scope === 'mine', fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status), fn ($q) => $q->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress]))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('assignee') && $scope !== 'mine', fn ($q) => $q->where('assigned_to', $request->assignee))
            ->orderByRaw('due_date is null')->orderBy('due_date')->paginate(25)->withQueryString();

        return view('admin.tasks.index', ['tasks' => $tasks, 'scope' => $scope] + $this->options());
    }

    public function create(Request $request): View
    {
        $task = new Task([
            'priority' => 'medium', 'status' => TaskStatus::Pending, 'assigned_to' => $request->user()->id,
            'due_date' => now()->addDays(2),
        ]);

        // Pre-link to an application or lead: ?application=APP-… or ?lead=ID
        if ($request->filled('application')) {
            $task->taskable()->associate(Application::where('application_no', $request->application)->firstOrFail());
        } elseif ($request->filled('lead')) {
            $task->taskable()->associate(Lead::findOrFail($request->integer('lead')));
        }

        return view('admin.tasks.form', ['task' => $task] + $this->options());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $task = Task::create($data + ['created_by' => $request->user()->id]);
        $this->notifyAssignee($task, $request->user());

        return $this->back($task)->with('success', 'Task created.');
    }

    public function edit(Task $task): View
    {
        return view('admin.tasks.form', ['task' => $task] + $this->options());
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $previous = $task->assigned_to;
        $data = $this->validated($request);
        $data['completed_at'] = ($data['status'] ?? null) === TaskStatus::Completed->value ? ($task->completed_at ?? now()) : null;
        $task->update($data);

        if ($task->assigned_to !== $previous) {
            $this->notifyAssignee($task, $request->user());
        }

        return $this->back($task)->with('success', 'Task updated.');
    }

    public function complete(Task $task): RedirectResponse
    {
        $task->update(['status' => TaskStatus::Completed, 'completed_at' => now()]);

        return back()->with('success', 'Task completed.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->whereIn('user_type', ['staff', 'professional'])],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'status' => ['required', new Enum(TaskStatus::class)],
            'due_date' => ['nullable', 'date'],
            'taskable_type' => ['nullable', Rule::in(['application', 'lead'])],
            'taskable_id' => ['nullable', 'integer'],
        ]);

        // Map the short type name to the model class and verify the record exists.
        $class = ['application' => Application::class, 'lead' => Lead::class][$data['taskable_type'] ?? ''] ?? null;
        $data['taskable_type'] = $class && ! empty($data['taskable_id']) && $class::whereKey($data['taskable_id'])->exists() ? $class : null;
        $data['taskable_id'] = $data['taskable_type'] ? $data['taskable_id'] : null;

        return $data;
    }

    private function back(Task $task): RedirectResponse
    {
        return match (true) {
            $task->taskable instanceof Application => redirect()->to(route('admin.applications.show', $task->taskable).'#tab-tasks'),
            $task->taskable instanceof Lead => redirect()->route('admin.leads.show', $task->taskable),
            default => redirect()->route('admin.tasks.index'),
        };
    }

    private function notifyAssignee(Task $task, User $actor): void
    {
        if ($task->assigned_to && $task->assigned_to !== $actor->id) {
            $task->assignee?->notify(new AssignedToYou('Task "'.$task->title.'"', route('admin.tasks.index'), 'bi-check2-square'));
        }
    }

    private function options(): array
    {
        return [
            'staff' => User::backoffice()->where('status', 'active')->orderBy('name')->pluck('name', 'id'),
            'statuses' => TaskStatus::options(),
            'priorities' => Task::PRIORITIES,
        ];
    }
}
