<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;

/** Result of a gateway call, independent of the gateway used. */
final class GatewayResponse
{
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?string $transactionId = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $message = null,
        public readonly array $meta = [],
    ) {}

    public static function paid(string $transactionId, ?string $message = null, array $meta = []): self
    {
        return new self(PaymentStatus::Paid, $transactionId, null, $message, $meta);
    }

    public static function pending(?string $transactionId = null, ?string $message = null, array $meta = []): self
    {
        return new self(PaymentStatus::Pending, $transactionId, null, $message, $meta);
    }

    public static function redirect(string $url, array $meta = []): self
    {
        return new self(PaymentStatus::Pending, null, $url, null, $meta);
    }

    public static function failed(string $message, array $meta = []): self
    {
        return new self(PaymentStatus::Failed, null, null, $message, $meta);
    }
}
