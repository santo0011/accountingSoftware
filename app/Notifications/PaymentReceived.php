<?php

namespace App\Notifications;

use App\Models\Payment;

class PaymentReceived extends AppNotification
{
    public function __construct(public Payment $payment) {}

    protected function title(): string
    {
        return 'Payment received';
    }

    protected function message(): string
    {
        $for = $this->payment->application ? ' for application '.$this->payment->application->application_no : '';

        return 'We have received your payment of '.money($this->payment->amount).$for.'. Your invoice is available in the portal.';
    }

    protected function url(object $notifiable): ?string
    {
        return $notifiable->isCustomer() ? route('portal.payments.index') : route('admin.payments.index');
    }

    protected function icon(): string
    {
        return 'bi-credit-card';
    }

    protected function color(): string
    {
        return 'success';
    }
}
