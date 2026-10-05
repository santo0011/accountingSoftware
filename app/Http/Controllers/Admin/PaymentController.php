<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:payments.view', only: ['index']),
            new Middleware('permission:payments.manage', only: ['record', 'confirm', 'fail']),
            new Middleware('permission:payments.refund', only: ['refund']),
        ];
    }

    public function __construct(private PaymentService $payments) {}

    public function index(Request $request): View
    {
        $query = Payment::with('customer.user:id,name', 'application:id,application_no', 'invoice:id,invoice_no')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('payment_no', 'like', $term)->orWhere('transaction_id', 'like', $term)
                    ->orWhereHas('application', fn ($a) => $a->where('application_no', 'like', $term))
                    ->orWhereHas('customer.user', fn ($u) => $u->where('name', 'like', $term)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->input('method')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        $totals = [
            'paid' => (clone $query)->where('status', PaymentStatus::Paid)->sum('amount'),
            'pending' => (clone $query)->where('status', PaymentStatus::Pending)->sum('amount'),
            'refunded' => (clone $query)->where('status', PaymentStatus::Refunded)->sum('refunded_amount'),
        ];

        return view('admin.payments.index', [
            'payments' => $query->latest()->paginate(per_page(25))->withQueryString(),
            'totals' => $totals,
        ]);
    }

    /** Record an offline payment against an application. */
    public function record(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.max((float) $application->total, 1)],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:60'],
        ]);

        if ($application->isPaid()) {
            return back()->with('error', 'This application is already paid.');
        }

        $this->payments->recordOffline($application, (float) $data['amount'], $data['method'], $data['reference'] ?? null, $request->user());

        return redirect()->to(route('admin.applications.show', $application).'#tab-payments')->with('success', 'Payment recorded and invoice marked paid.');
    }

    /** Confirm a payment the customer reported (UPI / bank transfer). */
    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Only pending payments can be confirmed.');
        }

        $this->payments->markPaid($payment, $payment->transaction_id, $payment->method, $request->user());

        return back()->with('success', "Payment {$payment->payment_no} confirmed.");
    }

    public function fail(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        if ($payment->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Only pending payments can be rejected.');
        }

        $this->payments->markFailed($payment, $data['reason'], $request->user());

        return back()->with('success', "Payment {$payment->payment_no} marked as failed.");
    }

    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->payments->refund($payment, (float) $data['amount'], $data['reason'], $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Refund recorded.');
    }
}
