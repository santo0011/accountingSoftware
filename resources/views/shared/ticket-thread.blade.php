{{-- Conversation of a support ticket. Expects $ticket (messages loaded) and $area ('portal'|'admin'). --}}
<div class="chat">
    @foreach ($ticket->messages as $msg)
        @php($mine = $msg->user_id === auth()->id())
        <div class="chat-msg {{ $mine ? 'mine' : '' }} {{ $msg->is_internal ? 'internal' : '' }}">
            <div class="meta">
                <strong class="text-navy">{{ $msg->user?->name ?? 'User' }}</strong>
                @if ($msg->user && ! $msg->user->isCustomer())<span class="badge badge-soft-primary ms-1">Team</span>@endif
                @if ($msg->is_internal)<span class="badge badge-soft-warning ms-1">Internal note</span>@endif
                · {{ $msg->created_at->format('d M Y, h:i A') }}
            </div>
            {!! nl2br(e($msg->message)) !!}
            @if ($msg->attachment_path)
                <div class="mt-2"><a href="{{ route($area.'.support.attachment', $msg) }}" class="small fw-semibold"><i class="bi bi-paperclip"></i> {{ $msg->attachment_name }}</a></div>
            @endif
        </div>
    @endforeach
</div>
