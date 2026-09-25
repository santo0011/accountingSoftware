@props(['label', 'value', 'icon' => 'bi-graph-up', 'color' => '', 'href' => null, 'hint' => null])
@php($tag = $href ? 'a' : 'div')
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'stat-card']) }}>
    <span class="icon-bubble {{ $color }}"><i class="bi {{ $icon }}"></i></span>
    <div class="min-w-0">
        <div class="value">{{ $value }}</div>
        <div class="label">{{ $label }}</div>
        @if ($hint)<div class="trend text-muted">{{ $hint }}</div>@endif
    </div>
</{{ $tag }}>
