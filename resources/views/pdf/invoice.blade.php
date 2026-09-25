<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_no }}</title>
    <style>
        /* DomPDF supports a limited CSS subset — keep it simple. DejaVu Sans renders the ₹ sign. */
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10.5px; color: #1f2937; margin: 0; }
        .header { border-bottom: 3px solid #0b2a4a; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 22px; font-weight: bold; color: #0b2a4a; }
        .brand span { color: #16a34a; }
        .muted { color: #6b7280; }
        .title { font-size: 18px; font-weight: bold; color: #0b2a4a; text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding: 0; }
        .box { background: #f5f7fa; padding: 10px 12px; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 3px; }
        .items th { background: #0b2a4a; color: #fff; padding: 7px 8px; text-align: left; font-size: 9.5px; }
        .items td { padding: 8px; border-bottom: 1px solid #e3e8ef; }
        .right { text-align: right; }
        .totals td { padding: 5px 8px; }
        .totals .grand td { font-size: 13px; font-weight: bold; color: #0b2a4a; border-top: 2px solid #0b2a4a; }
        .status { display: inline-block; padding: 3px 10px; font-weight: bold; font-size: 10px; }
        .paid { background: #e8f7ee; color: #128a3e; }
        .unpaid { background: #fef3c7; color: #92400e; }
        .other { background: #eef2f7; color: #475569; }
        .footer { margin-top: 30px; border-top: 1px solid #e3e8ef; padding-top: 10px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
@php
    $name = setting('company_name');
    $parts = preg_match('/^([A-Z][a-z]+)([A-Z].*)$/', $name, $m) ? [$m[1], $m[2]] : [$name, ''];
    $statusClass = match ($invoice->status->value) { 'paid' => 'paid', 'unpaid' => 'unpaid', default => 'other' };
@endphp

<table class="header">
    <tr>
        <td>
            <div class="brand">{{ $parts[0] }}<span>{{ $parts[1] }}</span></div>
            <div><strong>{{ setting('company_legal_name', $name) }}</strong></div>
            <div class="muted">{{ setting('company_address') }}</div>
            <div class="muted">{{ setting('company_phone') }} · {{ setting('company_email') }}</div>
            @if (setting('company_gstin'))<div>GSTIN: <strong>{{ setting('company_gstin') }}</strong> · PAN: {{ setting('company_pan') }}</div>@endif
        </td>
        <td style="width: 40%; vertical-align: top;">
            <div class="title">TAX INVOICE</div>
            <div class="right" style="margin-top:6px">Invoice No: <strong>{{ $invoice->invoice_no }}</strong></div>
            <div class="right">Date: {{ $invoice->invoice_date->format('d M Y') }}</div>
            @if ($invoice->due_date)<div class="right">Due: {{ $invoice->due_date->format('d M Y') }}</div>@endif
            <div class="right" style="margin-top:6px"><span class="status {{ $statusClass }}">{{ strtoupper($invoice->status->label()) }}</span></div>
        </td>
    </tr>
</table>

<table class="meta" style="margin-bottom: 18px">
    <tr>
        <td style="width: 50%; padding-right: 8px">
            <div class="box">
                <div class="label">Billed to</div>
                <strong>{{ $invoice->billing_name }}</strong><br>
                {{ $invoice->customer->user->name }}<br>
                @if ($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
                @if ($invoice->billing_gstin)GSTIN: {{ $invoice->billing_gstin }}<br>@endif
                {{ $invoice->customer->user->email }}
            </div>
        </td>
        <td style="width: 50%; padding-left: 8px">
            <div class="box">
                <div class="label">Details</div>
                Customer ID: {{ $invoice->customer->customer_code }}<br>
                @if ($invoice->application)Application: {{ $invoice->application->application_no }}<br>@endif
                Place of supply: {{ $invoice->place_of_supply ?? '—' }}<br>
                Tax type: {{ (float) $invoice->igst > 0 ? 'IGST (inter-state)' : 'CGST + SGST (intra-state)' }}
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr><th style="width:4%">#</th><th>Description</th><th>SAC</th><th class="right">Rate</th><th class="right">Discount</th><th class="right">Taxable</th><th class="right">GST %</th></tr>
    </thead>
    <tbody>
    @foreach ($invoice->items as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->sac_code }}</td>
            <td class="right">{{ money($item->rate) }}</td>
            <td class="right">{{ money($item->discount) }}</td>
            <td class="right">{{ money($item->amount) }}</td>
            <td class="right">{{ rtrim(rtrim(number_format($item->tax_rate, 2), '0'), '.') }}%</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table style="margin-top: 12px">
    <tr>
        <td style="width: 55%; vertical-align: top; padding-right: 12px">
            @if ($bank = setting('bank_details'))
                <div class="box"><div class="label">Payment details</div>{!! nl2br(e($bank)) !!}</div>
            @endif
        </td>
        <td style="width: 45%">
            <table class="totals">
                <tr><td>Subtotal</td><td class="right">{{ money($invoice->subtotal) }}</td></tr>
                @if ((float) $invoice->discount > 0)<tr><td>Discount</td><td class="right">- {{ money($invoice->discount) }}</td></tr>@endif
                @if ((float) $invoice->igst > 0)
                    <tr><td>IGST</td><td class="right">{{ money($invoice->igst) }}</td></tr>
                @else
                    <tr><td>CGST</td><td class="right">{{ money($invoice->cgst) }}</td></tr>
                    <tr><td>SGST</td><td class="right">{{ money($invoice->sgst) }}</td></tr>
                @endif
                <tr class="grand"><td>Total</td><td class="right">{{ money($invoice->total) }}</td></tr>
                @php($paid = $invoice->payments->where('status', \App\Enums\PaymentStatus::Paid)->sum('amount'))
                @if ($paid > 0)<tr><td class="muted">Amount paid</td><td class="right muted">{{ money($paid) }}</td></tr>@endif
            </table>
        </td>
    </tr>
</table>

<div class="footer">
    {{ setting('invoice_terms') }}<br>
    This is a computer-generated invoice and does not require a signature.
</div>
</body>
</html>
