@props(['eyebrow' => null, 'title', 'sub' => null, 'align' => 'center'])
<div class="section-head {{ $align === 'start' ? 'text-start' : '' }}">
    @if ($eyebrow)<span class="eyebrow">{{ $eyebrow }}</span>@endif
    <h2 class="section-title">{{ $title }}</h2>
    @if ($sub)<p class="section-sub mb-0">{{ $sub }}</p>@endif
</div>
