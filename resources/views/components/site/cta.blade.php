@props([
    'title' => 'Need Help With Your Business?',
    'text' => 'Get professional support from registration to business growth.',
])
<section class="section-sm">
    <div class="container">
        <div class="cta-photo" style="--cta-img: url('{{ asset('images/site/cta.webp') }}')">
            <div class="cta-photo-inner">
                <h2>{{ $title }}</h2>
                <p>{{ $text }}</p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('site.services.index') }}" class="btn btn-cta btn-lg">Get Started <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="tel:{{ preg_replace('/\s+/', '', (string) setting('company_phone')) }}" class="btn btn-light btn-lg"><i class="bi bi-telephone me-1"></i> {{ setting('company_phone') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
