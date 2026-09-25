<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = $request->user()->customer->invoices()
            ->with('application:id,application_no,service_id', 'application.service:id,name')
            ->latest('invoice_date')->latest('id')->paginate(15);

        return view('portal.invoices.index', compact('invoices'));
    }

    public function pdf(Invoice $invoice, InvoiceService $invoices): Response
    {
        $this->authorize('view', $invoice);

        return $invoices->pdf($invoice)->download($invoice->invoice_no.'.pdf');
    }
}
