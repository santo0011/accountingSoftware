<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Test gateway for local development and demos: every payment succeeds instantly.
 * Disabled unless PAYMENT_SANDBOX_ENABLED=true.
 */
class SandboxGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'sandbox';
    }

    public function label(): string
    {
        return 'Test Payment (Sandbox)';
    }

    public function checkoutRules(): array
    {
        return [];
    }

    public function initiate(Payment $payment, array $input): GatewayResponse
    {
        return GatewayResponse::paid('SBX'.strtoupper(Str::random(12)), 'Sandbox payment successful.');
    }

    public function handleCallback(Payment $payment, Request $request): GatewayResponse
    {
        return GatewayResponse::paid($payment->transaction_id ?? 'SBX'.strtoupper(Str::random(12)));
    }

    public function refund(Payment $payment, float $amount): bool
    {
        return true;
    }
}
