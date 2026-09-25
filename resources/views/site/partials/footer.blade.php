@php($tel = preg_replace('/\s+/', '', (string) setting('company_phone')))
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <x-brand class="mb-3" />
                <p class="mb-4" style="max-width:320px">One platform for all your business needs — registration, tax, compliance and growth.</p>
                <div class="social">
                    @foreach (['facebook' => 'bi-facebook', 'linkedin' => 'bi-linkedin', 'instagram' => 'bi-instagram', 'x' => 'bi-twitter-x', 'youtube' => 'bi-youtube'] as $key => $icon)
                        @if ($link = setting('social_'.$key))
                            <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($key) }}"><i class="bi {{ $icon }}"></i></a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <h4>Company</h4>
                <ul>
                    <li><a href="{{ route('site.about') }}">About Us</a></li>
                    <li><a href="{{ route('site.pricing') }}">Pricing</a></li>
                    <li><a href="{{ route('site.faq') }}">FAQ</a></li>
                    <li><a href="{{ route('site.contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <h4>Services</h4>
                <ul>
                    <li><a href="{{ route('site.categories.show', 'business-registration') }}">Business Registration</a></li>
                    <li><a href="{{ route('site.categories.show', 'gst-tax') }}">Tax &amp; Compliance</a></li>
                    <li><a href="{{ route('site.categories.show', 'trademark-ipr') }}">Trademark</a></li>
                    <li><a href="{{ route('site.categories.show', 'business-technology') }}">Technology</a></li>
                    <li><a href="{{ route('site.services.index') }}">All Services</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('site.privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('site.terms') }}">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('site.refund') }}">Refund Policy</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <h4>Contact</h4>
                <ul class="footer-contact">
                    <li><i class="bi bi-telephone"></i><a href="tel:{{ $tel }}">{{ setting('company_phone') }}</a></li>
                    <li><i class="bi bi-envelope"></i><a href="mailto:{{ setting('company_email') }}">{{ setting('company_email') }}</a></li>
                    <li><i class="bi bi-geo-alt"></i><span>{{ \Illuminate\Support\Str::afterLast((string) setting('company_address'), ', ') ?: setting('company_state') }}</span></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} {{ setting('company_legal_name', setting('company_name')) }}. All rights reserved.</span>
            <span><a href="{{ route('site.sitemap') }}">Sitemap</a></span>
        </div>
    </div>
</footer>
