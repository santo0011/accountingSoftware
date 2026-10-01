@php([$icon, $title, $text] = $promo)
@php($phone = setting('company_phone'))
{{-- Help card shown on the right of each mega menu --}}
<div class="growth-promo">
    <span class="growth-promo-ico"><i class="bi {{ $icon }}"></i></span>
    <strong>{{ $title }}</strong>
    <p>{{ $text }}</p>
    <a href="{{ route('site.contact') }}" class="btn btn-cta btn-sm w-100">Talk to an Expert <i class="bi bi-arrow-right ms-1"></i></a>
    @if ($phone)<a href="tel:{{ preg_replace('/\s+/', '', (string) $phone) }}" class="growth-promo-tel"><i class="bi bi-telephone"></i> {{ $phone }}</a>@endif
</div>
