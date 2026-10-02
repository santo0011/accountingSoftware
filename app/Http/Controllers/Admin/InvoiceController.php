<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:invoices.view')];
    }

    public function index(Request $request): View
    {
        $query = Invoice::with('customer.user:id,name', 'application:id,application_no')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('invoice_no', 'like', '%'.$request->q.'%')->orWhere('billing_name', 'like', '%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('invoice_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('invoice_date', '<=', $request->to));

        $totals = (clone $query)->toBase()->selectRaw('sum(total) as total, sum(cgst + sgst + igst) as tax')->first();

        return view('admin.invoices.index', [
            'invoices' => $query->latest('invoice_date')->latest('id')->paginate(per_page(25))->withQueryString(),
            'totals' => $totals,
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('items', 'customer.user', 'application.service', 'payments');

        return view('admin.invoices.show', compact('invoice'));
    }

    public function pdf(Request $request, Invoice $invoice, InvoiceService $invoices): Response
    {
        $pdf = $invoices->pdf($invoice);

        return $request->boolean('download') ? $pdf->download($invoice->invoice_no.'.pdf') : $pdf->stream($invoice->invoice_no.'.pdf');
    }
}
