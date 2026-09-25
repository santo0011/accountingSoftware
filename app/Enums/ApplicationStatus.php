<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    use HasOptions;

    case New = 'new';
    case DocumentsPending = 'documents_pending';
    case UnderReview = 'under_review';
    case DocumentsVerified = 'documents_verified';
    case PaymentPending = 'payment_pending';
    case Processing = 'processing';
    case Filed = 'filed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /** Customer-facing timeline stages, in order. */
    public const TIMELINE = [
        1 => 'Application Submitted',
        2 => 'Documents Under Review',
        3 => 'Documents Verified',
        4 => 'Processing Started',
        5 => 'Government / Professional Filing',
        6 => 'Processing',
        7 => 'Completed',
    ];

    public function color(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::DocumentsPending, self::PaymentPending => 'warning',
            self::UnderReview, self::Processing, self::InProgress => 'info',
            self::DocumentsVerified, self::Filed => 'teal',
            self::Completed => 'success',
            self::Rejected => 'danger',
            self::Cancelled => 'secondary',
        };
    }

    /** Which timeline stage (1-7) this status represents. */
    public function stage(): int
    {
        return match ($this) {
            self::New, self::DocumentsPending, self::PaymentPending, self::Rejected, self::Cancelled => 1,
            self::UnderReview => 2,
            self::DocumentsVerified => 3,
            self::Processing => 4,
            self::Filed => 5,
            self::InProgress => 6,
            self::Completed => 7,
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::Rejected, self::Cancelled], true);
    }

    /** @return list<self> */
    public static function active(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => ! $s->isClosed()));
    }
}
