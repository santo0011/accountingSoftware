<?php

namespace App\Notifications;

use App\Models\ComplianceRecord;

class ComplianceDue extends AppNotification
{
    public function __construct(public ComplianceRecord $record) {}

    protected function title(): string
    {
        return 'Compliance due: '.$this->record->title;
    }

    protected function message(): string
    {
        $days = $this->record->daysLeft();
        $when = $days < 0 ? abs($days).' day(s) overdue' : ($days === 0 ? 'due today' : "due in {$days} day(s)");

        return "{$this->record->title}".($this->record->period_label ? " ({$this->record->period_label})" : '')
            ." is {$when} — due date {$this->record->due_date->format('d M Y')}.";
    }

    protected function url(object $notifiable): ?string
    {
        return $notifiable->isCustomer() ? route('portal.compliance.index') : route('admin.compliance.index');
    }

    protected function icon(): string
    {
        return 'bi-calendar-event';
    }

    protected function color(): string
    {
        return $this->record->daysLeft() < 0 ? 'danger' : 'warning';
    }
}
