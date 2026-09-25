<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    use HasOptions;

    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Cancelled => 'secondary',
            self::Refunded => 'dark',
        };
    }
}
