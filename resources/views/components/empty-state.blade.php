@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'text' => null])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <i class="bi {{ $icon }}"></i>
    <div class="fw-semibold text-navy">{{ $title }}</div>
    @if ($text)<div class="small mt-1">{{ $text }}</div>@endif
    @if (! $slot->isEmpty())<div class="mt-3">{{ $slot }}</div>@endif
</div>
