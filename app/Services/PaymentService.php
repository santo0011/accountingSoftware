<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceived;
use App\Services\Payments\GatewayResponse;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Payment lifecycle — independent of which gateway processes the money. */
class PaymentService
{
    public function __construct(
        private PaymentGatewayManager $gateways,
        private NumberGenerator $numbers,
        private ApplicationService $applications,
    ) {}

    /** Customer checkout: creates a payment and hands it to the chosen gateway. */
    public function checkout(Application $application, string $gatewayName, array $input, User $actor): array
    {
        if ($application->isPaid()) {
            throw new RuntimeException('This application is already paid.');
        }

        $gateway = $this->gateways->get($gatewayName);

        $payment = Payment::create([
            'payment_no' => $this->numbers->next('payment'),
            'customer_id' => $application->customer_id,
            'application_id' => $application->id,
            'invoice_id' => $application->invoice?->id,
            'amount' => $application->total,
            'currency' => setting('currency', 'INR'),
            'method' => $input['method'] ?? null,
            'gateway' => $gateway->name(),
            'status' => PaymentStatus::Pending,
            'recorded_by' => $actor->id,
        ]);

        $response = $gateway->initiate($payment, $input);
        $this->apply($payment, $response, $actor);

        return [$payment->fresh(), $response];
    }

    /** Apply a gateway outcome to the payment and everything linked to it. */
    public function apply(Payment $payment, GatewayResponse $response, ?User $actor = null): void
    {
        match ($response->status) {
            PaymentStatus::Paid => $this->markPaid($payment, $response->transactionId, $payment->method, $actor, $response->meta),
            PaymentStatus::Failed => $payment->update(['status' => PaymentStatus::Failed, 'remarks' => $response->message, 'meta' => $response->meta]),
            default => $payment->update(['transaction_id' => $response->transactionId ?? $payment->transaction_id, 'meta' => $response->meta ?: $payment->meta]),
        };
    }

    public function markPaid(Payment $payment, ?string $transactionId, ?string $method, ?User $actor, array $meta = []): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return;
        }

        DB::transaction(function () use ($payment, $transactionId, $method, $actor, $meta) {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'transaction_id' => $transactionId ?: $payment->transaction_id,
                'method' => $method ?: $payment->method,
                'paid_at' => now(),
                'meta' => array_merge($payment->meta ?? [], $meta, $actor ? ['verified_by' => $actor->id] : []),
            ]);

            $payment->invoice?->update(['status' => InvoiceStatus::Paid]);

            if ($application = $payment->application) {
                $application->update(['payment_status' => PaymentStatus::Paid]);
                if ($application->status === ApplicationStatus::PaymentPending) {
                    $this->applications->changeStatus($application, ApplicationStatus::UnderReview, $actor, 'Payment received', notify: false);
                }
            }
        });

        if ($actor && ! $actor->isCustomer()) {
            activity('payments')->performedOn($payment)->causedBy($actor)->log("Marked payment {$payment->payment_no} as paid");
        }

        $payment->customer->user->notify(new PaymentReceived($payment));
    }

    public function markFailed(Payment $payment, string $reason, User $actor): void
    {
        $payment->update(['status' => PaymentStatus::Failed, 'remarks' => $reason]);
        activity('payments')->performedOn($payment)->causedBy($actor)->log("Marked payment {$payment->payment_no} as failed: {$reason}");
    }

    /** Staff records a payment received offline (cash, cheque, bank transfer). */
    public function recordOffline(Application $application, float $amount, string $method, ?string $reference, User $actor): Payment
    {
        $payment = Payment::create([
            'payment_no' => $this->numbers->next('payment'),
            'customer_id' => $application->customer_id,
            'application_id' => $application->id,
            'invoice_id' => $application->invoice?->id,
            'amount' => $amount,
            'currency' => setting('currency', 'INR'),
            'method' => $method,
            'gateway' => 'manual',
            'transaction_id' => $reference,
            'status' => PaymentStatus::Pending,
            'recorded_by' => $actor->id,
        ]);

        $this->markPaid($payment, $reference, $method, $actor);

        return $payment;
    }

    public function refund(Payment $payment, float $amount, string $reason, User $actor): void
    {
        if ($payment->status !== PaymentStatus::Paid) {
            throw new RuntimeException('Only paid payments can be refunded.');
        }
        if ($amount <= 0 || $amount > (float) $payment->amount) {
            throw new RuntimeException('Refund amount must be between 0 and the paid amount.');
        }
        if (! $this->gateways->forPayment($payment->gateway)->refund($payment, $amount)) {
            throw new RuntimeException('The payment gateway rejected the refund.');
        }

        DB::transaction(function () use ($payment, $amount, $reason) {
            $payment->update(['status' => PaymentStatus::Refunded, 'refunded_amount' => $amount, 'remarks' => $reason]);
            $payment->invoice?->update(['status' => InvoiceStatus::Refunded]);
            $payment->application?->update(['payment_status' => PaymentStatus::Refunded]);
        });

        activity('payments')->performedOn($payment)->causedBy($actor)->log('Refunded '.money($amount)." on {$payment->payment_no}: {$reason}");
    }
}
