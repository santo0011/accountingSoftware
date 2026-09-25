@props(['service', 'cta' => 'Learn More', 'price' => true, 'tag' => true])
{{-- Image-first service card: photo, name, one short line, price, one button. Whole card is clickable. --}}
<article class="img-card">
    <div class="img-card-media">
        <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" loading="lazy" decoding="async" width="800" height="534">
        @if (! $tag)
        @elseif ($service->hasDiscount())
            <span class="img-card-tag">Save {{ $service->discountPercent() }}%</span>
        @elseif ($service->isRecurring())
            <span class="img-card-tag muted">{{ $service->intervalLabel() }}</span>
        @endif
    </div>
    <div class="img-card-body">
        <h3 class="img-card-title"><a href="{{ route('site.services.show', $service->slug) }}" class="stretched-link">{{ $service->name }}</a></h3>
        <p class="img-card-text">{{ $service->cardText() }}</p>
        <div class="img-card-foot">
            @if ($price && $service->effectivePrice() > 0)
                <span class="img-card-price"><small>From</small> {{ money($service->effectivePrice(), false) }}</span>
            @else
                <span></span>
            @endif
            <span class="img-card-cta">{{ $cta }} <i class="bi bi-arrow-right"></i></span>
        </div>
    </div>
</article>
