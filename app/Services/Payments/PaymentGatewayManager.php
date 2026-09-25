<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use InvalidArgumentException;

/** Resolves the enabled payment gateways configured in config/payments.php. */
class PaymentGatewayManager
{
    /** @return array<string, PaymentGateway> */
    public function enabled(): array
    {
        $gateways = [];
        foreach (config('payments.gateways') as $name => $config) {
            if ($config['enabled'] ?? false) {
                $gateways[$name] = app($config['class']);
            }
        }

        return $gateways;
    }

    public function get(string $name): PaymentGateway
    {
        $gateway = $this->enabled()[$name] ?? null;

        if (! $gateway) {
            throw new InvalidArgumentException("Payment gateway [$name] is not enabled.");
        }

        return $gateway;
    }

    /** Gateway used for a stored payment, even if since disabled (for refunds/callbacks). */
    public function forPayment(string $name): PaymentGateway
    {
        $class = config("payments.gateways.$name.class");

        if (! $class) {
            throw new InvalidArgumentException("Unknown payment gateway [$name].");
        }

        return app($class);
    }
}
