<?php

namespace App\Notifications;

use App\Enums\ApplicationStatus;
use App\Models\Application;

class ApplicationStatusChanged extends AppNotification
{
    public function __construct(public Application $application, public ?string $remarks = null) {}

    protected function title(): string
    {
        return $this->application->status === ApplicationStatus::Completed
            ? 'Application completed'
            : 'Application status updated';
    }

    protected function message(): string
    {
        $text = "Application {$this->application->application_no} ({$this->application->service->name}) is now \"{$this->application->status->label()}\".";

        return $this->remarks ? $text.' Note: '.$this->remarks : $text;
    }

    protected function url(object $notifiable): ?string
    {
        return $this->applicationUrl($notifiable, $this->application->application_no);
    }

    protected function icon(): string
    {
        return $this->application->status === ApplicationStatus::Completed ? 'bi-patch-check' : 'bi-arrow-repeat';
    }

    protected function color(): string
    {
        return $this->application->status->color();
    }
}
