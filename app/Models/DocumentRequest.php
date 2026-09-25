<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fulfilled_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ApplicationDocument::class, 'application_document_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('fulfilled_at');
    }
}
