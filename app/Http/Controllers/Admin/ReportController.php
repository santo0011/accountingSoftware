<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Payment;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:reports.view')];
    }

    public function index(Request $request, ReportService $reports): View
    {
        [$from, $to] = $this->range($request);

        $payments = Payment::where('status', PaymentStatus::Paid)->whereBetween('paid_at', [$from, $to]);

        return view('admin.reports.index', [
            'from' => $from, 'to' => $to,
            'summary' => [
                'revenue' => (clone $payments)->sum('amount'),
                'payments' => (clone $payments)->count(),
                'applications' => Application::whereBetween('created_at', [$from, $to])->count(),
                'new_customers' => Customer::whereBetween('created_at', [$from, $to])->count(),
                'leads' => Lead::whereBetween('created_at', [$from, $to])->count(),
                'converted' => Lead::whereBetween('created_at', [$from, $to])->where('status', 'converted')->count(),
            ],
            'revenue' => $reports->revenueByMonth(max(1, min(24, (int) $from->diffInMonths($to) + 1)), $to),
            'byStatus' => $reports->applicationsByStatus($from, $to),
            'topServices' => $reports->topServices(10, $from, $to),
            'leadSources' => $reports->leadsBy('source', $from, $to),
            'leadStatuses' => $reports->leadsBy('status', $from, $to),
        ]);
    }

    /** CSV export of applications, payments, leads or customers for the selected period. */
    public function export(Request $request, string $type): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        [$headers, $query, $map] = match ($type) {
            'applications' => [
                ['Application No', 'Date', 'Customer', 'Service', 'Status', 'Payment', 'Total', 'Assigned To'],
                Application::with('customer.user:id,name', 'service:id,name', 'staff:id,name')->whereBetween('created_at', [$from, $to]),
                fn ($a) => [$a->application_no, $a->created_at->format('Y-m-d'), $a->customer->user->name, $a->service->name, $a->status->label(), $a->payment_status->label(), $a->total, $a->staff?->name],
            ],
            'payments' => [
                ['Payment No', 'Date', 'Customer', 'Application', 'Method', 'Reference', 'Amount', 'Status'],
                Payment::with('customer.user:id,name', 'application:id,application_no')->whereBetween('created_at', [$from, $to]),
                fn ($p) => [$p->payment_no, ($p->paid_at ?? $p->created_at)->format('Y-m-d'), $p->customer->user->name, $p->application?->application_no, $p->methodLabel(), $p->transaction_id, $p->amount, $p->status->label()],
            ],
            'leads' => [
                ['Date', 'Name', 'Email', 'Phone', 'Company', 'Service', 'Source', 'Status', 'Assigned To'],
                Lead::with('service:id,name', 'assignee:id,name')->whereBetween('created_at', [$from, $to]),
                fn ($l) => [$l->created_at->format('Y-m-d'), $l->name, $l->email, $l->phone, $l->company, $l->service?->name, $l->sourceLabel(), $l->status->label(), $l->assignee?->name],
            ],
            'customers' => [
                ['Customer Code', 'Joined', 'Name', 'Email', 'Mobile', 'City', 'State', 'Applications'],
                Customer::with('user:id,name,email,mobile')->withCount('applications')->whereBetween('created_at', [$from, $to]),
                fn ($c) => [$c->customer_code, $c->created_at->format('Y-m-d'), $c->user->name, $c->user->email, $c->user->mobile, $c->city, $c->state, $c->applications_count],
            ],
            default => abort(404),
        };

        activity('reports')->causedBy($request->user())->log("Exported {$type} report ({$from->toDateString()} to {$to->toDateString()})");

        return response()->streamDownload(function () use ($headers, $query, $map) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, $headers);
            $query->chunkById(500, function ($rows) use ($out, $map) {
                foreach ($rows as $row) {
                    // Prevent spreadsheet formula injection.
                    fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v, $map($row)));
                }
            });
            fclose($out);
        }, "{$type}-{$from->format('Ymd')}-{$to->format('Ymd')}.csv", ['Content-Type' => 'text/csv']);
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function range(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);

        $from = $request->filled('from') ? CarbonImmutable::parse($request->from)->startOfDay() : CarbonImmutable::now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to') ? CarbonImmutable::parse($request->to)->endOfDay() : CarbonImmutable::now()->endOfDay();

        return [$from, $to];
    }
}
