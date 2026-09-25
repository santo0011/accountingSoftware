<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'payment_status' => PaymentStatus::class,
            'form_data' => 'array',
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'application_no';
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

    public function customerService(): BelongsTo
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'assigned_professional_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class)->latest();
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', array_map(fn ($s) => $s->value, ApplicationStatus::active()));
    }

    /** Limit to what a back-office user may see: everything, or only what is assigned to them. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->can('applications.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('assigned_staff_id', $user->id);
            if ($user->professional) {
                $q->orWhere('assigned_professional_id', $user->professional->id);
            }
        });
    }
}
