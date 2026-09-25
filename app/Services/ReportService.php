<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\ComplianceStatus;
use App\Enums\DocumentStatus;
use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ComplianceRecord;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Aggregates for the admin dashboard and reports. Kept DB-agnostic (MySQL / SQLite). */
class ReportService
{
    public function dashboardStats(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'customers' => Customer::count(),
            'new_customers' => Customer::where('created_at', '>=', $monthStart)->count(),
            'new_leads' => Lead::where('status', LeadStatus::New)->count(),
            'active_applications' => Application::active()->count(),
            'completed_applications' => Application::where('status', ApplicationStatus::Completed)->count(),
            'pending_documents' => ApplicationDocument::where('type', 'customer')->whereIn('status', [DocumentStatus::Pending, DocumentStatus::UnderReview])->count(),
            'pending_payments' => Payment::where('status', PaymentStatus::Pending)->count()
                + Application::where('payment_status', PaymentStatus::Pending)->whereNotIn('status', ['cancelled', 'rejected'])->whereDoesntHave('payments', fn ($q) => $q->where('status', PaymentStatus::Pending))->count(),
            'monthly_revenue' => (float) Payment::where('status', PaymentStatus::Paid)->where('paid_at', '>=', $monthStart)->sum('amount'),
            'upcoming_compliance' => ComplianceRecord::whereIn('status', [ComplianceStatus::Upcoming, ComplianceStatus::DueSoon])->whereDate('due_date', '<=', now()->addDays(30))->count(),
            'overdue_compliance' => ComplianceRecord::where('status', ComplianceStatus::Overdue)->count(),
        ];
    }

    /** Paid revenue per month for the last $months months. @return array{labels: list<string>, values: list<float>} */
    public function revenueByMonth(int $months = 12, ?CarbonImmutable $to = null): array
    {
        $to = ($to ?? CarbonImmutable::now())->endOfMonth();
        $from = $to->subMonths($months - 1)->startOfMonth();

        $payments = Payment::where('status', PaymentStatus::Paid)->whereBetween('paid_at', [$from, $to])->get(['amount', 'paid_at']);
        $grouped = $payments->groupBy(fn ($p) => $p->paid_at->format('Y-m'))->map(fn ($g) => round($g->sum('amount'), 2));

        $labels = $values = [];
        for ($m = $from; $m->lte($to); $m = $m->addMonth()) {
            $labels[] = $m->format('M y');
            $values[] = (float) ($grouped[$m->format('Y-m')] ?? 0);
        }

        return compact('labels', 'values');
    }

    /** @return array<string, int> status label => count */
    public function applicationsByStatus(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $counts = Application::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->select('status', DB::raw('count(*) as total'))->groupBy('status')->toBase()->pluck('total', 'status');

        $result = [];
        foreach (ApplicationStatus::cases() as $status) {
            if ($counts[$status->value] ?? 0) {
                $result[$status->label()] = (int) $counts[$status->value];
            }
        }

        return $result;
    }

    public function topServices(int $limit = 10, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        return Application::query()
            ->when($from, fn ($q) => $q->where('applications.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('applications.created_at', '<=', $to))
            ->join('services', 'services.id', '=', 'applications.service_id')
            ->select('services.name', DB::raw('count(*) as applications'), DB::raw("sum(case when applications.payment_status = 'paid' then applications.total else 0 end) as revenue"))
            ->groupBy('services.id', 'services.name')->orderByDesc('applications')->limit($limit)->get();
    }

    /** @return array<string, int> */
    public function leadsBy(string $column, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        return Lead::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->select($column, DB::raw('count(*) as total'))->groupBy($column)->toBase()->pluck('total', $column)
            ->mapWithKeys(fn ($total, $key) => [ucwords(str_replace('_', ' ', (string) $key)) => (int) $total])->all();
    }
}
