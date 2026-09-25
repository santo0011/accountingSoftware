<?php

namespace App\Models;

use App\Enums\ComplianceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceRecord extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ComplianceStatus::class,
            'due_date' => 'date',
            'reminder_date' => 'date',
            'reminded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ComplianceType::class, 'compliance_type_id');
    }

    public function customerService(): BelongsTo
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', '!=', ComplianceStatus::Completed->value);
    }

    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_date, false);
    }
}
