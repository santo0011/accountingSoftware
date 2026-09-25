@props(['category', 'label' => null, 'count' => null])
{{-- Photo card with the name over a dark gradient; one short line; "View Services". --}}
<a href="{{ route('site.categories.show', $category->slug) }}" class="cat-card">
    <img src="{{ $category->imageUrl() }}" alt="{{ $label ?? $category->name }}" loading="lazy" decoding="async" width="800" height="534">
    <span class="cat-card-overlay"></span>
    <span class="cat-card-icon"><i class="bi {{ $category->icon }}"></i></span>
    <span class="cat-card-body">
        <span class="cat-card-title">{{ $label ?? $category->name }}</span>
        <span class="cat-card-text">{{ $category->tagline }}</span>
        <span class="cat-card-cta">View Services @if ($count)<span class="cat-card-count">{{ $count }}</span>@endif <i class="bi bi-arrow-right"></i></span>
    </span>
</a>
