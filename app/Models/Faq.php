<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const GROUPS = ['general' => 'General', 'payments' => 'Payments', 'process' => 'Process', 'account' => 'Account'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)->orderBy('sort_order');
    }
}
