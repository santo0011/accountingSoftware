<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Offline payment: the customer pays by UPI / bank transfer and submits the
 * reference number. The payment stays "pending" until staff verify it.
 */
class ManualGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'manual';
    }

    public function label(): string
    {
        return 'UPI / Bank Transfer';
    }

    public function checkoutRules(): array
    {
        return [
            'method' => ['required', Rule::in(['upi', 'bank_transfer'])],
            'reference' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Za-z0-9\-\/]+$/'],
        ];
    }

    public function initiate(Payment $payment, array $input): GatewayResponse
    {
        return GatewayResponse::pending(
            $input['reference'],
            'Thank you! We will verify your payment reference and confirm within one working day.',
            ['submitted_at' => now()->toDateTimeString()],
        );
    }

    public function handleCallback(Payment $payment, Request $request): GatewayResponse
    {
        return GatewayResponse::pending($payment->transaction_id);
    }

    public function refund(Payment $payment, float $amount): bool
    {
        return true; // Refunded offline by the accounts team.
    }
}
