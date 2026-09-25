<?php

namespace App\Enums;

enum TicketStatus: string
{
    use HasOptions;

    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingForCustomer = 'waiting_for_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'primary',
            self::InProgress => 'info',
            self::WaitingForCustomer => 'warning',
            self::Resolved => 'success',
            self::Closed => 'secondary',
        };
    }
}
