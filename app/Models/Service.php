<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use Auditable, SoftDeletes;

    public const INTERVALS = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half_yearly' => 'Half-yearly',
        'yearly' => 'Yearly',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'who_needs' => 'array',
            'benefits' => 'array',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'gst_rate' => 'decimal:2',
            'is_featured' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function complianceType(): BelongsTo
    {
        return $this->belongsTo(ComplianceType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ServiceDocument::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceField::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ServiceProcessStep::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /** Price the customer actually pays (before GST). */
    public function effectivePrice(): float
    {
        return (float) ($this->hasDiscount() ? $this->discount_price : $this->price);
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && (float) $this->discount_price > 0 && (float) $this->discount_price < (float) $this->price;
    }

    public function discountPercent(): int
    {
        return $this->hasDiscount() ? (int) round(100 - ($this->discount_price / $this->price * 100)) : 0;
    }

    public function isRecurring(): bool
    {
        return $this->billing_type === 'recurring';
    }

    public function intervalLabel(): ?string
    {
        return self::INTERVALS[$this->recurring_interval] ?? null;
    }

    /** Card / hero image: uploaded image → bundled default → category image. */
    public function imageUrl(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }
        if (is_file(public_path("images/services/{$this->slug}.webp"))) {
            return asset("images/services/{$this->slug}.webp");
        }

        return $this->category?->imageUrl() ?? asset('images/site/placeholder.webp');
    }

    /** One short line for cards. */
    public function cardText(): string
    {
        return $this->tagline ?: \Illuminate\Support\Str::limit((string) $this->short_description, 60);
    }

    public function iconClass(): string
    {
        return $this->icon ?: ($this->category?->icon ?: 'bi-briefcase');
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->name.' Online in India';
    }

    public function metaDescription(): string
    {
        return $this->seo_description ?: (string) $this->short_description;
    }
}
