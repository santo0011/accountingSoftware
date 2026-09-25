<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\TicketUpdated;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(private NumberGenerator $numbers) {}

    public function open(Customer $customer, array $data, ?UploadedFile $attachment, User $author): SupportTicket
    {
        $ticket = DB::transaction(function () use ($customer, $data, $attachment, $author) {
            $ticket = SupportTicket::create([
                'ticket_no' => $this->numbers->next('ticket'),
                'customer_id' => $customer->id,
                'application_id' => $data['application_id'] ?? null,
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => TicketStatus::Open,
                'last_reply_at' => now(),
            ]);

            $this->addMessage($ticket, $author, $data['message'], $attachment, false);

            return $ticket;
        });

        $staff = User::permission('support.view_all')->where('status', 'active')->get();
        Notification::send($staff, new TicketUpdated($ticket, 'created'));

        return $ticket;
    }

    public function reply(SupportTicket $ticket, User $author, string $message, ?UploadedFile $attachment, bool $internal = false, ?TicketStatus $status = null): SupportMessage
    {
        $reply = DB::transaction(function () use ($ticket, $author, $message, $attachment, $internal, $status) {
            $reply = $this->addMessage($ticket, $author, $message, $attachment, $internal);

            $newStatus = $status ?? ($author->isCustomer()
                ? TicketStatus::Open
                : ($internal ? $ticket->status : TicketStatus::WaitingForCustomer));

            $ticket->update(['status' => $newStatus, 'last_reply_at' => now()]);

            return $reply;
        });

        if (! $internal) {
            if ($author->isCustomer()) {
                $ticket->assignee?->notify(new TicketUpdated($ticket));
            } else {
                $ticket->customer->user->notify(new TicketUpdated($ticket));
            }
        }

        return $reply;
    }

    public function updateStatus(SupportTicket $ticket, TicketStatus $status, ?int $assignedTo, User $actor): void
    {
        $statusChanged = $ticket->status !== $status;
        $ticket->update(['status' => $status, 'assigned_to' => $assignedTo]);

        if ($statusChanged) {
            $ticket->customer->user->notify(new TicketUpdated($ticket, 'status'));
        }

        activity('support')->performedOn($ticket)->causedBy($actor)->log("Ticket {$ticket->ticket_no} set to {$status->label()}");
    }

    private function addMessage(SupportTicket $ticket, User $author, string $message, ?UploadedFile $attachment, bool $internal): SupportMessage
    {
        $path = $attachment?->storeAs("support/{$ticket->id}", Str::uuid().'.'.strtolower($attachment->getClientOriginalExtension()), 'local');

        return $ticket->messages()->create([
            'user_id' => $author->id,
            'message' => $message,
            'attachment_path' => $path,
            'attachment_name' => $attachment ? Str::limit($attachment->getClientOriginalName(), 250, '') : null,
            'is_internal' => $internal,
        ]);
    }

    public function downloadAttachment(SupportMessage $message)
    {
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name);
    }
}
