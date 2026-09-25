<?php

namespace App\Notifications;

/** Tells a staff member / professional that a record was assigned to them. */
class AssignedToYou extends AppNotification
{
    public function __construct(
        public string $what,
        public string $link,
        public string $iconClass = 'bi-person-check',
    ) {}

    protected function title(): string
    {
        return 'New assignment';
    }

    protected function message(): string
    {
        return "{$this->what} has been assigned to you.";
    }

    protected function url(object $notifiable): ?string
    {
        return $this->link;
    }

    protected function icon(): string
    {
        return $this->iconClass;
    }
}
