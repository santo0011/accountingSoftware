<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceField extends Model
{
    public const TYPES = ['text' => 'Text', 'textarea' => 'Paragraph', 'number' => 'Number', 'email' => 'Email', 'date' => 'Date', 'select' => 'Dropdown'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['options' => 'array', 'is_required' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Validation rules for the customer's answer to this field. */
    public function rules(): array
    {
        $rules = [$this->is_required ? 'required' : 'nullable'];

        return array_merge($rules, match ($this->type) {
            'number' => ['numeric'],
            'email' => ['email', 'max:255'],
            'date' => ['date'],
            'select' => ['in:'.implode(',', $this->options ?? [])],
            'textarea' => ['string', 'max:2000'],
            default => ['string', 'max:255'],
        });
    }
}
