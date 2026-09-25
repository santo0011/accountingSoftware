<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use Auditable;

    public const METHODS = [
        'upi' => 'UPI',
        'bank_transfer' => 'Bank Transfer (NEFT/RTGS/IMPS)',
        'card' => 'Credit / Debit Card',
        'netbanking' => 'Net Banking',
        'cheque' => 'Cheque',
        'cash' => 'Cash',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'payment_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? ucfirst((string) $this->method ?: '—');
    }
}
