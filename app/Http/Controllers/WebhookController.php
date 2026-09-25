<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gateway redirect / webhook endpoint. Each gateway verifies its own signature
 * inside handleCallback(); this controller only applies the outcome.
 */
class WebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway, Payment $payment, PaymentGatewayManager $gateways, PaymentService $payments): Response
    {
        abort_unless($payment->gateway === $gateway, 404);

        if ($payment->status === PaymentStatus::Pending) {
            $payments->apply($payment, $gateways->forPayment($gateway)->handleCallback($payment, $request));
        }

        if ($request->isMethod('get') && $request->user()) {
            return redirect()->route('portal.payments.index')->with('success', 'Payment status updated.');
        }

        return response()->json(['status' => $payment->fresh()->status->value]);
    }
}
