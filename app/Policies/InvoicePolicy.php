<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isCustomer()) {
            return $user->customer?->id === $invoice->customer_id;
        }

        return $user->can('invoices.view');
    }
}
