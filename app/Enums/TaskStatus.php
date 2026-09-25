<?php

namespace App\Enums;

enum TaskStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::InProgress => 'info',
            self::Completed => 'success',
            self::Cancelled => 'dark',
        };
    }
}
