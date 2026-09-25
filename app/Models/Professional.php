<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use Auditable, SoftDeletes;

    public const TYPES = [
        'ca' => 'Chartered Accountant (CA)',
        'cs' => 'Company Secretary (CS)',
        'lawyer' => 'Lawyer / Advocate',
        'tax_consultant' => 'Tax Consultant',
        'business_consultant' => 'Business Consultant',
    ];

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_professional_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->professional_type] ?? $this->professional_type;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
