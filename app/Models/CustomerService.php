<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A service a customer has engaged us for; recurring ones drive compliance records. */
class CustomerService extends Model
{
    public const STATUSES = ['active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'next_due_date' => 'date', 'price' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function complianceRecords(): HasMany
    {
        return $this->hasMany(ComplianceRecord::class);
    }

    public function isRecurring(): bool
    {
        return $this->billing_type === 'recurring';
    }

    public function statusColor(): string
    {
        return ['active' => 'success', 'paused' => 'warning', 'completed' => 'primary', 'cancelled' => 'secondary'][$this->status] ?? 'secondary';
    }
}
