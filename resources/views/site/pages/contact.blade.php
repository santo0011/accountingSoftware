@extends('layouts.site')

@section('title', (string) ('Contact Us — Talk to a Business Expert'))
@section('meta_description', (string) ('Contact our experts for company registration, GST, trademark, compliance and legal services. We respond within one working day.'))

@section('content')
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li><li class="breadcrumb-item active">Contact</li></ol></nav>
        <h1>Talk to an expert</h1>
        <p class="section-sub mb-0">Tell us what you need — we'll recommend the right service and call you back within one working day.</p>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('site.contact.store') }}" novalidate>
                            @csrf
                            <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">
                            <div class="row">
                                <x-form.input name="name" label="Your name" required col="col-md-6 mb-3" />
                                <x-form.input name="phone" label="Mobile number" type="tel" required maxlength="10" prepend="+91" col="col-md-6 mb-3" />
                                <x-form.input name="email" label="Email" type="email" required col="col-md-6 mb-3" />
                                <x-form.input name="company" label="Company / business name" col="col-md-6 mb-3" />
                                <x-form.select name="service_id" label="Service you are interested in" :options="$services" :value="request('service')" placeholder="Not sure / other" col="col-md-6 mb-3" />
                                <x-form.input name="city" label="City" col="col-md-6 mb-3" />
                                <x-form.textarea name="message" label="How can we help?" required rows="4" col="col-12 mb-3" />
                            </div>
                            <button type="submit" class="btn btn-cta btn-lg">Send Message</button>
                            <p class="small text-muted mt-3 mb-0"><i class="bi bi-lock me-1"></i>Your details are safe with us. We never share them.</p>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="d-grid gap-3">
                    @foreach ([
                        ['bi-telephone', 'Call us', setting('company_phone'), 'tel:'.preg_replace('/\s+/', '', setting('company_phone'))],
                        ['bi-envelope', 'Email us', setting('company_email'), 'mailto:'.setting('company_email')],
                        ['bi-whatsapp', 'WhatsApp', '+'.setting('company_whatsapp'), 'https://wa.me/'.setting('company_whatsapp')],
                        ['bi-geo-alt', 'Visit us', setting('company_address'), null],
                        ['bi-clock', 'Working hours', setting('business_hours'), null],
                    ] as [$icon, $label, $value, $link])
                        <div class="feature-box align-items-center">
                            <span class="icon-bubble"><i class="bi {{ $icon }}"></i></span>
                            <div>
                                <div class="small text-muted">{{ $label }}</div>
                                @if ($link)<a href="{{ $link }}" class="fw-semibold text-navy">{{ $value }}</a>@else<div class="fw-semibold text-navy">{{ $value }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
