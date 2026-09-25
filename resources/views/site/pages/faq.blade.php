@extends('layouts.site')

@section('title', (string) ('Frequently Asked Questions'))
@section('meta_description', (string) ('Answers to common questions about our business registration, tax, compliance and legal services, payments and process.'))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">FAQ</li></ol></nav>
        <h1>Frequently asked questions</h1>
        <p class="section-sub mb-0">Everything you need to know about working with {{ setting('company_name') }}.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                @forelse ($faqs as $group => $items)
                    <h2 class="h5 mb-3 {{ $loop->first ? '' : 'mt-5' }}">{{ \App\Models\Faq::GROUPS[$group] ?? ucfirst($group) }}</h2>
                    <div class="accordion" id="faq-{{ $group }}">
                        @foreach ($items as $faq)
                            <div class="accordion-item">
                                <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f{{ $faq->id }}">{{ $faq->question }}</button></h3>
                                <div id="f{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ $group }}"><div class="accordion-body text-muted">{{ $faq->answer }}</div></div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <x-empty-state icon="bi-question-circle" title="No FAQs yet" />
                @endforelse
            </div>
            <div class="col-lg-4">
                <div class="card position-sticky" style="top:96px">
                    <div class="card-body text-center p-4">
                        <span class="icon-bubble lg mb-3"><i class="bi bi-chat-dots"></i></span>
                        <h3 class="h5">Still have questions?</h3>
                        <p class="text-muted small">Our experts are available {{ setting('business_hours') }}.</p>
                        <a href="{{ route('site.contact') }}" class="btn btn-cta w-100 mb-2">Contact Us</a>
                        <a href="tel:{{ preg_replace('/\s+/', '', setting('company_phone')) }}" class="btn btn-outline-primary w-100"><i class="bi bi-telephone me-1"></i>{{ setting('company_phone') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->flatten()->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endpush
