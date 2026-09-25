<?php

namespace App\Notifications;

use App\Models\DocumentRequest;

class DocumentRequested extends AppNotification
{
    public function __construct(public DocumentRequest $request) {}

    protected function title(): string
    {
        return 'Additional document required';
    }

    protected function message(): string
    {
        $text = "Please upload \"{$this->request->document_name}\" for application {$this->request->application->application_no}.";

        return $this->request->note ? $text.' '.$this->request->note : $text;
    }

    protected function url(object $notifiable): ?string
    {
        return $this->applicationUrl($notifiable, $this->request->application->application_no);
    }

    protected function icon(): string
    {
        return 'bi-cloud-upload';
    }

    protected function color(): string
    {
        return 'warning';
    }
}
