@extends('layouts.'.$area)
@section('title', (string) ('Notifications'))

@section('content')
<x-page-header title="Notifications">
    @if (auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route($area.'.notifications.read-all') }}">@csrf<button class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i>Mark all as read</button></form>
    @endif
</x-page-header>

<div class="card">
    @forelse ($notifications as $n)
        <a href="{{ route($area.'.notifications.open', $n->id) }}" class="list-row {{ $n->read_at ? '' : 'bg-soft' }}">
            @php($color = ['success' => 'green', 'danger' => 'red', 'warning' => 'amber'][$n->data['color'] ?? ''] ?? '')
            <span class="icon-bubble sm {{ $color }}"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
            <div class="min-w-0 flex-grow-1">
                <div class="title">{{ $n->data['title'] ?? 'Notification' }} @unless ($n->read_at)<span class="badge bg-primary ms-1">New</span>@endunless</div>
                <div class="meta">{{ $n->data['message'] ?? '' }}</div>
            </div>
            <small class="text-muted text-nowrap">{{ $n->created_at->diffForHumans() }}</small>
        </a>
    @empty
        <x-empty-state icon="bi-bell-slash" title="No notifications yet" />
    @endforelse
</div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
