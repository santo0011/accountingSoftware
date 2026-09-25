@props(['service', 'cta' => 'Learn More', 'price' => true, 'tag' => true])
@php
    $category = $service->relationLoaded('category') ? $service->category : null;
    $showPrice = $price && $service->effectivePrice() > 0;
@endphp
{{-- Premium image-first service card. The whole card is clickable. --}}
<article class="svc-card">
    <div class="svc-media">
        <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" loading="lazy" decoding="async" width="800" height="534">
        @if ($category?->name)
            <span class="svc-chip">{{ $category->name }}</span>
        @endif
        @if ($tag && $service->isRecurring())
            <span class="svc-save muted"><i class="bi bi-arrow-repeat me-1"></i>{{ $service->intervalLabel() }}</span>
        @endif
    </div>
    <div class="svc-body">
        <span class="svc-icon"><i class="bi {{ $service->iconClass() }}"></i></span>
        <h3 class="svc-title"><a href="{{ route('site.services.show', $service->slug) }}" class="stretched-link">{{ $service->name }}</a></h3>
        <p class="svc-text">{{ $service->cardText() }}</p>
        <div class="svc-foot">
            @if ($showPrice)
                @php($symbol = setting('currency_symbol', '₹'))
                <div class="svc-price">
                    <span class="svc-price-row">
                        <span class="svc-amount"><span class="svc-cur">{{ $symbol }}</span>{{ \Illuminate\Support\Str::after(money($service->effectivePrice(), false), $symbol) }}</span>
                        @if ($service->hasDiscount())<s class="svc-was">{{ money($service->price, false) }}</s>@endif
                    </span>
                    <span class="svc-meta">
                        Starting price
                        @if ($service->hasDiscount())
                            <span class="svc-off" title="You save {{ money($service->price - $service->effectivePrice(), false) }}">{{ $service->discountPercent() }}% off</span>
                        @endif
                    </span>
                </div>
            @else
                <div class="svc-price">
                    <span class="svc-price-row"><span class="svc-amount quote">Get a Quote</span></span>
                    <span class="svc-meta">Custom pricing</span>
                </div>
            @endif
            <span class="svc-btn">{{ $cta === 'Learn More' ? 'Details' : $cta }} <i class="bi bi-arrow-right"></i></span>
        </div>
    </div>
</article>
