<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'proprietorship' => 'Proprietorship',
        'partnership' => 'Partnership Firm',
        'llp' => 'LLP',
        'private_limited' => 'Private Limited Company',
        'opc' => 'One Person Company',
        'public_limited' => 'Public Limited Company',
        'section8' => 'Section 8 / NGO',
        'trust' => 'Trust / Society',
        'startup' => 'Startup (not yet registered)',
        'other' => 'Other',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['incorporation_date' => 'date', 'is_primary' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->business_type] ?? ucfirst((string) $this->business_type);
    }
}
