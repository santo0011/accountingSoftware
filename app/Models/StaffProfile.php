<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    public const DEPARTMENTS = ['Operations', 'Tax & GST', 'Corporate Law', 'Trademark & IPR', 'Accounts', 'HR', 'Sales', 'Support', 'Technology', 'Management'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['joined_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
