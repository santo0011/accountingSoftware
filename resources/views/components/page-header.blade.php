@props(['title', 'subtitle' => null, 'back' => null])
<div class="page-header">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="small text-muted d-inline-flex align-items-center gap-1 mb-1"><i class="bi bi-arrow-left"></i> Back</a>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)<div class="sub">{{ $subtitle }}</div>@endif
    </div>
    @if (! $slot->isEmpty())
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
