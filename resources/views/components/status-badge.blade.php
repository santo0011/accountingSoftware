@props(['status', 'label' => null, 'color' => null])
@php
    $color = $color ?? (is_object($status) && method_exists($status, 'color') ? $status->color() : 'secondary');
    $label = $label ?? (is_object($status) && method_exists($status, 'label') ? $status->label() : ucwords(str_replace('_', ' ', (string) $status)));
@endphp
<span {{ $attributes->merge(['class' => "badge badge-status badge-soft-{$color}"]) }}>{{ $label }}</span>
