<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const CATEGORIES = [
        'general' => 'General Query',
        'application' => 'Application Status',
        'documents' => 'Documents',
        'payment' => 'Payment / Invoice',
        'compliance' => 'Compliance',
        'technical' => 'Technical Issue',
    ];

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => TicketStatus::class, 'last_reply_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function priorityColor(): string
    {
        return ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'][$this->priority] ?? 'secondary';
    }
}
