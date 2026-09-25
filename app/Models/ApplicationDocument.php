<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class ApplicationDocument extends Model
{
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_DELIVERABLE = 'deliverable';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => DocumentStatus::class, 'reviewed_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function serviceDocument(): BelongsTo
    {
        return $this->belongsTo(ServiceDocument::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isDeliverable(): bool
    {
        return $this->type === self::TYPE_DELIVERABLE;
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function icon(): string
    {
        return match (true) {
            str_contains((string) $this->mime, 'pdf') => 'bi-file-earmark-pdf text-danger',
            str_contains((string) $this->mime, 'image') => 'bi-file-earmark-image text-info',
            str_contains((string) $this->mime, 'word') => 'bi-file-earmark-word text-primary',
            default => 'bi-file-earmark',
        };
    }
}
