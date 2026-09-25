<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:support.view')];
    }

    public function __construct(private TicketService $tickets) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $base = SupportTicket::query()->when(! $user->can('support.view_all'), fn ($q) => $q->where('assigned_to', $user->id));

        $tickets = (clone $base)->with('customer.user:id,name', 'assignee:id,name')->withCount('messages')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('ticket_no', 'like', '%'.$request->q.'%')->orWhere('subject', 'like', '%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status),
                fn ($q) => $q->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed]))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->latest('last_reply_at')->paginate(25)->withQueryString();

        return view('admin.support.index', [
            'tickets' => $tickets,
            'counts' => (clone $base)->toBase()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);
        $ticket->load('customer.user', 'application.service:id,name', 'assignee', 'messages.user:id,name,user_type');

        return view('admin.support.show', [
            'ticket' => $ticket,
            'staff' => User::backoffice()->where('status', 'active')->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('reply', $ticket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => DocumentService::fileRules(required: false),
            'internal' => ['nullable', 'boolean'],
            'status' => ['nullable', new Enum(TicketStatus::class)],
        ]);

        if (! $ticket->assigned_to) {
            $ticket->update(['assigned_to' => $request->user()->id]);
        }

        $this->tickets->reply($ticket, $request->user(), $data['message'], $request->file('attachment'),
            $request->boolean('internal'), isset($data['status']) ? TicketStatus::from($data['status']) : null);

        return back()->with('success', $request->boolean('internal') ? 'Internal note added.' : 'Reply sent to the customer.');
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($request->user()->can('support.manage') || $request->user()->can('reply', $ticket), 403);

        $data = $request->validate([
            'status' => ['required', new Enum(TicketStatus::class)],
            'priority' => ['required', Rule::in(array_keys(SupportTicket::PRIORITIES))],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->whereIn('user_type', ['staff', 'professional'])],
        ]);

        $ticket->update(['priority' => $data['priority']]);
        $this->tickets->updateStatus($ticket, TicketStatus::from($data['status']), $data['assigned_to'] ?? null, $request->user());

        return back()->with('success', 'Ticket updated.');
    }

    public function attachment(SupportMessage $message): StreamedResponse
    {
        $this->authorize('view', $message->ticket);

        return $this->tickets->downloadAttachment($message);
    }
}
