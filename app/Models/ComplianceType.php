<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceType extends Model
{
    public const FREQUENCIES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half_yearly' => 'Half-yearly',
        'yearly' => 'Yearly',
        'one_time' => 'One time',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function records(): HasMany
    {
        return $this->hasMany(ComplianceRecord::class);
    }
}
