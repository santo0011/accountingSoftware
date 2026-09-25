<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use Auditable, SoftDeletes;

    public const SOURCES = [
        'website' => 'Website',
        'contact_form' => 'Contact Form',
        'phone' => 'Phone Call',
        'whatsapp' => 'WhatsApp',
        'referral' => 'Referral',
        'social' => 'Social Media',
        'google_ads' => 'Google Ads',
        'walk_in' => 'Walk-in',
        'other' => 'Other',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => LeadStatus::class, 'next_followup_at' => 'datetime'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowup::class)->latest();
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst((string) $this->source);
    }
}
