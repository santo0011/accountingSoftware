<?php

namespace App\Enums;

enum DocumentStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case ReuploadRequired = 'reupload_required';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::UnderReview => 'info',
            self::Verified => 'success',
            self::Rejected => 'danger',
            self::ReuploadRequired => 'warning',
        };
    }
}
