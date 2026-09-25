<?php

namespace App\Notifications;

use App\Enums\DocumentStatus;
use App\Models\ApplicationDocument;

class DocumentReviewed extends AppNotification
{
    public function __construct(public ApplicationDocument $document) {}

    private function approved(): bool
    {
        return $this->document->status === DocumentStatus::Verified;
    }

    protected function title(): string
    {
        return $this->approved() ? 'Document approved' : 'Document needs attention';
    }

    protected function message(): string
    {
        $no = $this->document->application->application_no;

        return $this->approved()
            ? "Your document \"{$this->document->name}\" for application {$no} has been verified."
            : "Your document \"{$this->document->name}\" for application {$no} was {$this->document->status->label()}. Reason: {$this->document->rejection_reason}";
    }

    protected function url(object $notifiable): ?string
    {
        return $this->applicationUrl($notifiable, $this->document->application->application_no);
    }

    protected function icon(): string
    {
        return $this->approved() ? 'bi-file-earmark-check' : 'bi-file-earmark-x';
    }

    protected function color(): string
    {
        return $this->approved() ? 'success' : 'danger';
    }
}
