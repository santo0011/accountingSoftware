<?php

namespace App\Contracts\Payments;

use App\Models\Payment;
use App\Services\Payments\GatewayResponse;
use Illuminate\Http\Request;

/**
 * Contract every payment gateway (manual, Razorpay, etc.) implements.
 * Controllers and services only talk to this interface — never to a gateway SDK directly.
 */
interface PaymentGateway
{
    /** Short machine name stored on payments.gateway, e.g. "manual", "razorpay". */
    public function name(): string;

    /** Label shown to the customer on the checkout screen. */
    public function label(): string;

    /** Validation rules for the extra input this gateway needs on the checkout form. */
    public function checkoutRules(): array;

    /** Start a payment. May complete it immediately, leave it pending, or ask for a redirect. */
    public function initiate(Payment $payment, array $input): GatewayResponse;

    /** Handle a gateway redirect/webhook callback and report the outcome. */
    public function handleCallback(Payment $payment, Request $request): GatewayResponse;

    /** Refund all or part of a paid payment. Returns true when the gateway accepted the refund. */
    public function refund(Payment $payment, float $amount): bool;
}
