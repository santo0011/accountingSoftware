@props(['href' => null])
@php
    $name = setting('company_name', config('app.name'));
    // "BizSetu" -> "Biz" + accent "Setu"
    $parts = preg_match('/^([A-Z][a-z]+)([A-Z].*)$/', $name, $m) ? [$m[1], $m[2]] : [$name, ''];
@endphp
<a href="{{ $href ?? route('site.home') }}" {{ $attributes->merge(['class' => 'brand']) }} aria-label="{{ $name }} home">
    @if (setting('logo'))
        <img src="{{ storage_asset(setting('logo')) }}" alt="{{ $name }}">
    @else
        <span class="brand-mark"><i class="bi bi-bar-chart-steps"></i></span>
        <span class="brand-text">{{ $parts[0] }}<span class="accent">{{ $parts[1] }}</span></span>
    @endif
</a>
