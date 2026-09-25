<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\DocumentService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportController extends Controller
{
    public function __construct(private TicketService $tickets) {}

    public function index(Request $request): View
    {
        $tickets = $request->user()->customer->tickets()->withCount('messages')->latest('last_reply_at')->paginate(15);

        return view('portal.support.index', compact('tickets'));
    }

    public function create(Request $request): View
    {
        $applications = $request->user()->customer->applications()->with('service:id,name')->latest()->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->application_no.' — '.$a->service->name]);

        return view('portal.support.create', [
            'applications' => $applications,
            'selected' => $request->query('application'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user()->customer;

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(array_keys(SupportTicket::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(SupportTicket::PRIORITIES))],
            'application_id' => ['nullable', Rule::exists('applications', 'id')->where('customer_id', $customer->id)],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => DocumentService::fileRules(required: false),
        ]);

        $ticket = $this->tickets->open($customer, $data, $request->file('attachment'), $request->user());

        return redirect()->route('portal.support.show', $ticket)->with('success', "Ticket {$ticket->ticket_no} created. Our team will respond shortly.");
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['application:id,application_no', 'messages' => fn ($q) => $q->where('is_internal', false)->with('user:id,name,user_type')]);

        return view('portal.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('reply', $ticket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => DocumentService::fileRules(required: false),
        ]);

        $this->tickets->reply($ticket, $request->user(), $data['message'], $request->file('attachment'));

        return back()->with('success', 'Reply sent.');
    }

    public function attachment(SupportMessage $message): StreamedResponse
    {
        $this->authorize('view', $message->ticket);
        abort_if($message->is_internal, 404);

        return $this->tickets->downloadAttachment($message);
    }
}
