<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->isCustomer()) {
            return $user->customer?->id === $ticket->customer_id;
        }

        return $user->can('support.view_all') || ($user->can('support.view') && $ticket->assigned_to === $user->id);
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        if ($user->isCustomer()) {
            return $this->view($user, $ticket) && $ticket->status !== TicketStatus::Closed;
        }

        return $user->can('support.reply') && $this->view($user, $ticket);
    }

    public function manage(User $user, SupportTicket $ticket): bool
    {
        return $user->isBackoffice() && $user->can('support.manage');
    }
}
