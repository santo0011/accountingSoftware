<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    public const CHANNELS = ['call' => 'Phone Call', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'meeting' => 'Meeting'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['followup_at' => 'datetime'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
