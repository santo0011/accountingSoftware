<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    use HasOptions;

    case Upcoming = 'upcoming';
    case DueSoon = 'due_soon';
    case Overdue = 'overdue';
    case Completed = 'completed';

    public function color(): string
    {
        return match ($this) {
            self::Upcoming => 'info',
            self::DueSoon => 'warning',
            self::Overdue => 'danger',
            self::Completed => 'success',
        };
    }
}
