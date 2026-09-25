<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => TaskStatus::class, 'due_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday()
            && ! in_array($this->status, [TaskStatus::Completed, TaskStatus::Cancelled], true);
    }

    public function priorityColor(): string
    {
        return ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$this->priority] ?? 'secondary';
    }

    /** Human-readable link target of the related record, if any. */
    public function relatedLabel(): ?string
    {
        return match (true) {
            $this->taskable instanceof Application => 'Application '.$this->taskable->application_no,
            $this->taskable instanceof Lead => 'Lead: '.$this->taskable->name,
            $this->taskable instanceof ComplianceRecord => 'Compliance: '.$this->taskable->title,
            default => null,
        };
    }
}
