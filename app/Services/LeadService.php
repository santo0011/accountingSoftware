<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\AssignedToYou;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeadService
{
    public function __construct(private CustomerAccountService $accounts) {}

    public function create(array $data, ?User $actor = null): Lead
    {
        $lead = Lead::create($data + ['status' => LeadStatus::New]);
        $this->notifyAssignee($lead, null, $actor);

        return $lead;
    }

    public function update(Lead $lead, array $data, User $actor): Lead
    {
        $previous = $lead->assigned_to;
        $lead->update($data);
        $this->notifyAssignee($lead, $previous, $actor);

        return $lead;
    }

    public function addFollowup(Lead $lead, array $data, User $actor): void
    {
        DB::transaction(function () use ($lead, $data, $actor) {
            $lead->followups()->create([
                'user_id' => $actor->id,
                'channel' => $data['channel'],
                'remarks' => $data['remarks'],
                'followup_at' => now(),
            ]);

            $lead->update([
                'status' => $data['status'] ?? ($lead->status === LeadStatus::New ? LeadStatus::Contacted : $lead->status),
                'next_followup_at' => $data['next_followup_at'] ?? null,
            ]);

            if (! empty($data['next_followup_at'])) {
                $lead->tasks()->create([
                    'title' => 'Follow up with '.$lead->name,
                    'description' => $data['remarks'],
                    'assigned_to' => $lead->assigned_to ?? $actor->id,
                    'created_by' => $actor->id,
                    'priority' => 'medium',
                    'status' => 'pending',
                    'due_date' => $data['next_followup_at'],
                ]);
            }
        });
    }

    /** Convert a lead into a customer account and email them a set-password link. */
    public function convert(Lead $lead, User $actor): Customer
    {
        if ($lead->customer_id) {
            throw new RuntimeException('This lead has already been converted.');
        }
        if (! $lead->email) {
            throw new RuntimeException('Add an email address to the lead before converting.');
        }

        $existing = User::where('email', $lead->email)->first();
        if ($existing && ! $existing->isCustomer()) {
            throw new RuntimeException('This email belongs to a staff account.');
        }

        $customer = DB::transaction(function () use ($lead, $existing) {
            $customer = $existing?->customer ?? $this->accounts->create([
                'name' => $lead->name,
                'email' => $lead->email,
                'mobile' => $lead->phone && ! User::where('mobile', $lead->phone)->exists() ? $lead->phone : null,
                'business_name' => $lead->company,
                'city' => $lead->city,
                'source' => $lead->source,
            ], verified: true);

            $lead->update(['status' => LeadStatus::Converted, 'customer_id' => $customer->id]);

            return $customer;
        });

        if (! $existing) {
            $this->accounts->sendSetPasswordLink($customer->user);
        }

        activity('leads')->performedOn($lead)->causedBy($actor)->log("Converted lead to customer {$customer->customer_code}");

        return $customer;
    }

    private function notifyAssignee(Lead $lead, ?int $previous, ?User $actor): void
    {
        if ($lead->assigned_to && $lead->assigned_to !== $previous && $lead->assigned_to !== $actor?->id) {
            $lead->assignee?->notify(new AssignedToYou("Lead \"{$lead->name}\"", route('admin.leads.show', $lead), 'bi-person-plus'));
        }
    }
}
