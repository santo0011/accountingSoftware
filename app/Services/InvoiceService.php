<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Application;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/** GST-compliant pricing and invoices (CGST+SGST within the state, IGST otherwise). */
class InvoiceService
{
    public function __construct(private NumberGenerator $numbers) {}

    /**
     * @return array{amount: float, discount: float, taxable: float, tax_rate: float, cgst: float, sgst: float, igst: float, tax: float, total: float, intra_state: bool}
     */
    public function quote(Service $service, ?string $customerState = null): array
    {
        $amount = round((float) $service->price, 2);
        $taxable = round($service->effectivePrice(), 2);
        $discount = round($amount - $taxable, 2);
        $rate = (float) ($service->gst_rate ?? setting('default_tax', 18));
        $tax = round($taxable * $rate / 100, 2);

        $intra = $this->isIntraState($customerState);
        $cgst = $intra ? round($tax / 2, 2) : 0.0;
        $sgst = $intra ? round($tax - $cgst, 2) : 0.0;
        $igst = $intra ? 0.0 : $tax;

        return [
            'amount' => $amount, 'discount' => $discount, 'taxable' => $taxable, 'tax_rate' => $rate,
            'cgst' => $cgst, 'sgst' => $sgst, 'igst' => $igst, 'tax' => $tax,
            'total' => round($taxable + $tax, 2), 'intra_state' => $intra,
        ];
    }

    public function isIntraState(?string $customerState): bool
    {
        // Unknown state: treat as intra-state (the most common case for a local business).
        return ! $customerState || Str::lower(trim($customerState)) === Str::lower((string) setting('company_state', 'Maharashtra'));
    }

    public function placeOfSupply(Customer $customer, ?Business $business): ?string
    {
        return $business?->state ?: $customer->state;
    }

    public function createForApplication(Application $application): Invoice
    {
        $application->loadMissing('customer.user', 'business', 'service');
        $customer = $application->customer;
        $business = $application->business;
        $service = $application->service;
        $state = $this->placeOfSupply($customer, $business);
        $quote = $this->quote($service, $state);

        $invoice = Invoice::create([
            'invoice_no' => $this->numbers->next('invoice'),
            'customer_id' => $customer->id,
            'business_id' => $business?->id,
            'application_id' => $application->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'billing_name' => $business?->name ?: $customer->user->name,
            'billing_gstin' => $business?->gstin,
            'billing_address' => collect([$business?->address ?? $customer->address, $business?->city ?? $customer->city, $state])->filter()->implode(', ') ?: null,
            'place_of_supply' => $state,
            'subtotal' => $quote['amount'],
            'discount' => $quote['discount'],
            'cgst' => $quote['cgst'],
            'sgst' => $quote['sgst'],
            'igst' => $quote['igst'],
            'total' => $quote['total'],
            'status' => InvoiceStatus::Unpaid,
        ]);

        $invoice->items()->create([
            'service_id' => $service->id,
            'description' => $service->name.' — Application '.$application->application_no,
            'sac_code' => $service->sac_code ?: setting('default_sac_code'),
            'quantity' => 1,
            'rate' => $quote['amount'],
            'discount' => $quote['discount'],
            'tax_rate' => $quote['tax_rate'],
            'tax_amount' => $quote['tax'],
            'amount' => $quote['taxable'],
        ]);

        return $invoice;
    }

    public function pdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->loadMissing('items', 'customer.user', 'application.service', 'payments');

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])->setPaper('a4');
    }
}
