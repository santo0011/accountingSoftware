<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort_order')->orderBy('name');
    }

    public function activeServices(): HasMany
    {
        return $this->services()->where('status', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)->orderBy('sort_order');
    }

    /** Card / banner image: uploaded banner → bundled default → generic placeholder. */
    public function imageUrl(): string
    {
        if ($this->banner_image) {
            return asset('storage/'.$this->banner_image);
        }

        return is_file(public_path("images/categories/{$this->slug}.webp"))
            ? asset("images/categories/{$this->slug}.webp")
            : asset('images/site/placeholder.webp');
    }

    /** Small thumbnail for menus (falls back to the full image). */
    public function thumbUrl(): string
    {
        return ! $this->banner_image && is_file(public_path("images/categories/thumbs/{$this->slug}.webp"))
            ? asset("images/categories/thumbs/{$this->slug}.webp")
            : $this->imageUrl();
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->name.' Services';
    }
}
