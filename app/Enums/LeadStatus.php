<?php

namespace App\Enums;

enum LeadStatus: string
{
    use HasOptions;

    case New = 'new';
    case Contacted = 'contacted';
    case FollowUp = 'follow_up';
    case Converted = 'converted';
    case NotInterested = 'not_interested';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::Contacted => 'info',
            self::FollowUp => 'warning',
            self::Converted => 'success',
            self::NotInterested => 'secondary',
            self::Closed => 'dark',
        };
    }
}
