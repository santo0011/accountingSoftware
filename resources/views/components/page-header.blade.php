@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => null])
@php
    // "Back to services" / "Back to lead": taken from the back URL unless backLabel is given.
    if ($back && ! $backLabel) {
        $parts = array_values(array_filter(explode('/', (string) parse_url($back, PHP_URL_PATH))));
        $last = end($parts) ?: '';
        $name = is_numeric($last) ? \Illuminate\Support\Str::singular($parts[count($parts) - 2] ?? '') : $last;
        $backLabel = $name && ! in_array($name, ['admin', 'portal'], true) ? 'Back to '.str_replace('-', ' ', $name) : 'Back';
    }
@endphp
<div class="page-header">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="page-back"><span class="page-back-icon"><i class="bi bi-arrow-left"></i></span><span>{{ $backLabel }}</span></a>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)<div class="sub">{{ $subtitle }}</div>@endif
    </div>
    @if (! $slot->isEmpty())
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
