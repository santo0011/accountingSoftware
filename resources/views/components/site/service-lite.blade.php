@props(['service'])
{{-- Compact service card with a photo strip, used on the services listing. --}}
<a href="{{ route('site.services.show', $service->slug) }}" class="svc-lite">
    <span class="svc-lite-media">
        <img src="{{ $service->imageUrl() }}" alt="" loading="lazy" decoding="async" width="600" height="400">
        @if ($service->is_featured)<span class="svc-lite-tag hot">Popular</span>
        @elseif ($service->isRecurring())<span class="svc-lite-tag"><i class="bi bi-arrow-repeat"></i> {{ $service->intervalLabel() }}</span>@endif
    </span>
    <span class="svc-lite-top">
        <span class="svc-lite-icon"><i class="bi {{ $service->iconClass() }}"></i></span>
    </span>
    <strong class="svc-lite-name">{{ $service->name }}</strong>
    <span class="svc-lite-text">{{ $service->cardText() }}</span>
    <span class="svc-lite-foot">
        @if ($service->effectivePrice() > 0)
            <span class="svc-lite-price">
                <small>Starting at</small>
                <b>{{ money($service->effectivePrice(), false) }}</b>
                @if ($service->hasDiscount())<s>{{ money($service->price, false) }}</s><em>{{ $service->discountPercent() }}% off</em>@endif
            </span>
        @else
            <span class="svc-lite-price"><small>Pricing</small><b class="quote">Get a quote</b></span>
        @endif
        <span class="svc-lite-go"><i class="bi bi-arrow-right"></i></span>
    </span>
</a>
