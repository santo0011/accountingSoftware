<?php

namespace App\Services;

use App\Enums\ComplianceStatus;
use App\Models\Application;
use App\Models\Business;
use App\Models\ComplianceRecord;
use App\Models\ComplianceType;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\User;
use App\Notifications\ComplianceDue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Recurring compliance: subscriptions for recurring services generate one
 * compliance record per period; a daily job refreshes statuses and sends reminders.
 */
class ComplianceService
{
    /** When a service application completes, register the engagement (and its first due date if recurring). */
    public function activateFromApplication(Application $application): ?CustomerService
    {
        $service = $application->service;

        $subscription = $application->customerService ?? CustomerService::create([
            'customer_id' => $application->customer_id,
            'business_id' => $application->business_id,
            'service_id' => $service->id,
            'billing_type' => $service->billing_type,
            'recurring_interval' => $service->recurring_interval,
            'price' => $service->effectivePrice(),
            'start_date' => now()->toDateString(),
            'status' => $service->isRecurring() ? 'active' : 'completed',
        ]);

        $application->update(['customer_service_id' => $subscription->id]);

        if ($service->isRecurring() && $service->complianceType && ! $subscription->complianceRecords()->exists()) {
            $record = $this->createRecord($subscription->customer, $subscription->business, $service->complianceType, CarbonImmutable::now(), $subscription, $application->assigned_staff_id);
            $subscription->update(['next_due_date' => $record->due_date]);
        }

        return $subscription;
    }

    /** Create the compliance record for the period that contains $periodDate. */
    public function createRecord(Customer $customer, ?Business $business, ComplianceType $type, CarbonImmutable $periodDate, ?CustomerService $subscription = null, ?int $staffId = null): ComplianceRecord
    {
        [$start, $end, $label] = $this->period($type->frequency, $periodDate);
        $due = $this->dueDate($type, $end);

        return ComplianceRecord::create([
            'customer_id' => $customer->id,
            'business_id' => $business?->id,
            'compliance_type_id' => $type->id,
            'customer_service_id' => $subscription?->id,
            'title' => $type->name,
            'period_label' => $label,
            'due_date' => $due->toDateString(),
            'frequency' => $type->frequency,
            'reminder_date' => $due->subDays($type->reminder_days_before)->toDateString(),
            'status' => $this->statusFor($due, $type->reminder_days_before),
            'assigned_staff_id' => $staffId,
        ]);
    }

    public function complete(ComplianceRecord $record, User $actor, ?string $remarks = null): ?ComplianceRecord
    {
        return DB::transaction(function () use ($record, $actor, $remarks) {
            $record->update(['status' => ComplianceStatus::Completed, 'completed_at' => now(), 'remarks' => $remarks ?? $record->remarks]);
            activity('compliance')->performedOn($record)->causedBy($actor)->log("Completed {$record->title} {$record->period_label}");

            return $this->generateNext($record);
        });
    }

    /** For an active recurring subscription, create the next period's record. */
    public function generateNext(ComplianceRecord $record): ?ComplianceRecord
    {
        $subscription = $record->customerService;
        if (! $subscription || $subscription->status !== 'active' || $record->frequency === 'one_time') {
            return null;
        }

        [, $end] = $this->period($record->frequency, CarbonImmutable::parse($record->due_date)->subMonthsNoOverflow($record->type->due_month_offset));
        $nextPeriod = $end->addDay();

        $exists = ComplianceRecord::where('customer_service_id', $subscription->id)
            ->where('period_label', $this->period($record->frequency, $nextPeriod)[2])->exists();
        if ($exists) {
            return null;
        }

        $next = $this->createRecord($record->customer, $record->business, $record->type, $nextPeriod, $subscription, $record->assigned_staff_id);
        $subscription->update(['next_due_date' => $next->due_date]);

        return $next;
    }

    /** Daily: recompute Upcoming / Due Soon / Overdue. Returns number of records changed. */
    public function refreshStatuses(): int
    {
        $changed = 0;

        ComplianceRecord::with('type')->pending()->chunkById(200, function ($records) use (&$changed) {
            foreach ($records as $record) {
                $status = $this->statusFor(CarbonImmutable::parse($record->due_date), $record->type->reminder_days_before);
                if ($record->status !== $status) {
                    $record->update(['status' => $status]);
                    $changed++;
                }
            }
        });

        return $changed;
    }

    /** Daily: notify customers (and the assigned staff) whose reminder date has arrived. */
    public function sendReminders(): int
    {
        $sent = 0;

        ComplianceRecord::with('customer.user', 'staff', 'type')->pending()
            ->whereNull('reminded_at')->whereDate('reminder_date', '<=', now())
            ->chunkById(200, function ($records) use (&$sent) {
                foreach ($records as $record) {
                    $record->customer->user?->notify(new ComplianceDue($record));
                    $record->staff?->notify(new ComplianceDue($record));
                    $record->update(['reminded_at' => now()]);
                    $sent++;
                }
            });

        return $sent;
    }

    public function statusFor(CarbonImmutable $due, int $reminderDays): ComplianceStatus
    {
        $today = CarbonImmutable::today();

        return match (true) {
            $due->lt($today) => ComplianceStatus::Overdue,
            $due->lte($today->addDays(max($reminderDays, (int) setting('compliance_reminder_days', 7)))) => ComplianceStatus::DueSoon,
            default => ComplianceStatus::Upcoming,
        };
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string} start, end and label of the period containing $date */
    public function period(string $frequency, CarbonImmutable $date): array
    {
        // Indian financial year: April – March.
        $fyStartYear = $date->month >= 4 ? $date->year : $date->year - 1;
        $fyLabel = 'FY '.$fyStartYear.'-'.substr((string) ($fyStartYear + 1), -2);

        switch ($frequency) {
            case 'monthly':
                return [$date->startOfMonth(), $date->endOfMonth()->startOfDay(), $date->format('M Y')];
            case 'quarterly':
                $quarter = intdiv(($date->month + 8) % 12, 3) + 1; // Apr-Jun = Q1
                $start = CarbonImmutable::create($fyStartYear, 4, 1)->addMonths(($quarter - 1) * 3);

                return [$start, $start->addMonths(3)->subDay(), "Q{$quarter} {$fyLabel}"];
            case 'half_yearly':
                $half = in_array($date->month, [4, 5, 6, 7, 8, 9], true) ? 1 : 2;
                $start = CarbonImmutable::create($fyStartYear, $half === 1 ? 4 : 10, 1);

                return [$start, $start->addMonths(6)->subDay(), "H{$half} {$fyLabel}"];
            default: // yearly and one_time follow the financial year
                $start = CarbonImmutable::create($fyStartYear, 4, 1);

                return [$start, $start->addYear()->subDay(), $fyLabel];
        }
    }

    private function dueDate(ComplianceType $type, CarbonImmutable $periodEnd): CarbonImmutable
    {
        $month = $periodEnd->startOfMonth()->addMonthsNoOverflow($type->due_month_offset);

        return $month->setDay(min(max($type->due_day, 1), $month->daysInMonth));
    }
}
