<?php

namespace App\Enums;

enum PaymentStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'secondary',
        };
    }
}
