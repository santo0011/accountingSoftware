<?php

namespace App\Notifications;

use App\Models\Application;

class ApplicationSubmitted extends AppNotification
{
    public function __construct(public Application $application) {}

    protected function title(): string
    {
        return 'Application received';
    }

    protected function message(): string
    {
        return "Application {$this->application->application_no} for {$this->application->service->name} has been submitted successfully.";
    }

    protected function url(object $notifiable): ?string
    {
        return $this->applicationUrl($notifiable, $this->application->application_no);
    }

    protected function icon(): string
    {
        return 'bi-send-check';
    }
}
