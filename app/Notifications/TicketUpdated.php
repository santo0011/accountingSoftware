<?php

namespace App\Notifications;

use App\Models\SupportTicket;

class TicketUpdated extends AppNotification
{
    public function __construct(public SupportTicket $ticket, public string $event = 'reply') {}

    protected function title(): string
    {
        return match ($this->event) {
            'created' => 'New support ticket',
            'status' => 'Ticket status updated',
            default => 'New reply on your ticket',
        };
    }

    protected function message(): string
    {
        return match ($this->event) {
            'created' => "Ticket {$this->ticket->ticket_no}: {$this->ticket->subject}",
            'status' => "Ticket {$this->ticket->ticket_no} is now {$this->ticket->status->label()}.",
            default => "There is a new reply on ticket {$this->ticket->ticket_no}: {$this->ticket->subject}",
        };
    }

    protected function url(object $notifiable): ?string
    {
        return $notifiable->isCustomer()
            ? route('portal.support.show', $this->ticket->ticket_no)
            : route('admin.support.show', $this->ticket->ticket_no);
    }

    protected function icon(): string
    {
        return 'bi-headset';
    }
}
