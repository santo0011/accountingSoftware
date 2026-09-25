<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments, private PaymentGatewayManager $gateways) {}

    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        $dues = $customer->applications()->where('payment_status', PaymentStatus::Pending)->where('total', '>', 0)
            ->whereNotIn('status', [ApplicationStatus::Cancelled, ApplicationStatus::Rejected])
            ->with('service:id,name', 'payments')->latest()->get();

        $payments = $customer->payments()->with('application:id,application_no,service_id', 'application.service:id,name', 'invoice:id,invoice_no')
            ->latest()->paginate(15);

        return view('portal.payments.index', compact('dues', 'payments'));
    }

    public function checkout(Request $request, Application $application): View|RedirectResponse
    {
        if (! $request->user()->can('pay', $application)) {
            return redirect()->route('portal.applications.show', $application)->with('info', 'No payment is due for this application.');
        }

        $application->load('service', 'invoice.items', 'business');
        $awaitingVerification = $application->payments()->where('status', PaymentStatus::Pending)->whereNotNull('transaction_id')->latest()->first();

        return view('portal.payments.checkout', [
            'application' => $application,
            'gateways' => $this->gateways->enabled(),
            'awaitingVerification' => $awaitingVerification,
        ]);
    }

    public function pay(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('pay', $application);

        $enabled = $this->gateways->enabled();
        $request->validate(['gateway' => ['required', Rule::in(array_keys($enabled))]]);
        $gateway = $enabled[$request->input('gateway')];
        $input = $request->validate($gateway->checkoutRules());

        try {
            [$payment, $response] = $this->payments->checkout($application, $gateway->name(), $input, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($response->redirectUrl) {
            return redirect()->away($response->redirectUrl);
        }

        return match ($payment->status) {
            PaymentStatus::Paid => redirect()->route('portal.applications.show', $application)->with('success', 'Payment successful! Your invoice is ready to download.'),
            PaymentStatus::Failed => back()->with('error', $response->message ?? 'Payment failed. Please try again.'),
            default => redirect()->route('portal.payments.index')->with('success', $response->message ?? 'Payment submitted for verification.'),
        };
    }
}
