@extends('layouts.admin')
@section('title', (string) ('Settings'))

@php($v = fn ($key, $default = null) => old($key, $s[$key] ?? $default))

@section('content')
<x-page-header title="Settings" subtitle="Company details, billing, notifications, email and SEO." />

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" novalidate>
    @csrf @method('PUT')

    <ul class="nav nav-tabs mb-3" role="tablist">
        @foreach (['company' => 'Company', 'billing' => 'Billing & numbering', 'notifications' => 'Notifications', 'email' => 'Email (SMTP)', 'seo' => 'SEO & social', 'homepage' => 'Homepage'] as $id => $label)
            <li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab" data-bs-target="#tab-{{ $id }}" data-tab-name="{{ $id }}">{{ $label }}</button></li>
        @endforeach
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-company">
            <div class="card"><div class="card-body row">
                <x-form.input name="company_name" label="Company / brand name" :value="$v('company_name')" required col="col-md-6 mb-3" />
                <x-form.input name="company_legal_name" label="Legal name (on invoices)" :value="$v('company_legal_name')" col="col-md-6 mb-3" />
                <x-form.input name="tagline" label="Tagline" :value="$v('tagline')" col="col-md-12 mb-3" />
                <x-form.input name="company_email" type="email" label="Email" :value="$v('company_email')" required col="col-md-4 mb-3" />
                <x-form.input name="company_phone" label="Phone" :value="$v('company_phone')" required col="col-md-4 mb-3" />
                <x-form.input name="company_whatsapp" label="WhatsApp (with country code, digits only)" :value="$v('company_whatsapp')" placeholder="919876543210" col="col-md-4 mb-3" />
                <x-form.input name="company_address" label="Address" :value="$v('company_address')" col="col-md-8 mb-3" />
                <x-form.input name="company_state" label="State (for GST)" :value="$v('company_state')" required help="Same-state customers get CGST+SGST, others IGST." col="col-md-4 mb-3" />
                <x-form.input name="company_gstin" label="GSTIN" :value="$v('company_gstin')" col="col-md-4 mb-3" />
                <x-form.input name="company_pan" label="PAN" :value="$v('company_pan')" col="col-md-4 mb-3" />
                <x-form.input name="business_hours" label="Business hours" :value="$v('business_hours')" col="col-md-4 mb-3" />
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="logo">Logo</label>
                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp"
                        data-image-preview="#logoPreview" data-max-kb="2048">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="img-preview img-preview-sm mt-2" id="logoPreview">
                        <div class="img-preview-frame">
                            <img src="{{ ! empty($s['logo']) ? storage_asset($s['logo']) : '' }}" alt="Logo preview" data-preview-img @if (empty($s['logo'])) hidden @endif>
                            <span class="img-preview-empty" data-preview-empty @if (! empty($s['logo'])) hidden @endif>No logo uploaded — the text logo is used.</span>
                        </div>
                        <div class="img-preview-meta">
                            <span data-preview-info class="text-muted small"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto" data-preview-undo hidden><i class="bi bi-arrow-counterclockwise me-1"></i>Undo</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Favicon (PNG/ICO)</label>
                    <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept=".png,.ico">
                    @error('favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="tab-billing">
            <div class="card"><div class="card-body row">
                <x-form.input name="currency" label="Currency" :value="$v('currency', 'INR')" required col="col-md-3 mb-3" />
                <x-form.input name="currency_symbol" label="Symbol" :value="$v('currency_symbol', '₹')" required col="col-md-3 mb-3" />
                <x-form.select name="default_tax" label="Default GST rate" :options="[0 => '0%', 5 => '5%', 12 => '12%', 18 => '18%', 28 => '28%']" :value="(int) $v('default_tax', 18)" col="col-md-3 mb-3" />
                <x-form.input name="default_sac_code" label="Default SAC code" :value="$v('default_sac_code')" col="col-md-3 mb-3" />
                <x-form.input name="application_prefix" label="Application prefix" :value="$v('application_prefix', 'APP')" required help="APP-2026-000001" col="col-md-3 mb-3" />
                <x-form.input name="invoice_prefix" label="Invoice prefix" :value="$v('invoice_prefix', 'INV')" required col="col-md-3 mb-3" />
                <x-form.input name="payment_prefix" label="Payment prefix" :value="$v('payment_prefix', 'PAY')" required col="col-md-3 mb-3" />
                <x-form.input name="ticket_prefix" label="Ticket prefix" :value="$v('ticket_prefix', 'TKT')" required col="col-md-3 mb-3" />
                <x-form.textarea name="bank_details" label="Bank / UPI details (shown on checkout & invoice)" :value="$v('bank_details')" rows="5" col="col-md-6 mb-3" />
                <x-form.textarea name="invoice_terms" label="Invoice terms" :value="$v('invoice_terms')" rows="5" col="col-md-6 mb-3" />
            </div></div>
        </div>

        <div class="tab-pane fade" id="tab-notifications">
            <div class="card mb-3"><div class="card-body row">
                <x-form.check name="notify_email_enabled" label="Send email notifications (in addition to in-app notifications)" :checked="$v('notify_email_enabled', '1') === '1'" col="col-12 mb-3" />
                <x-form.input name="compliance_reminder_days" type="number" label="Compliance reminder (days before due date)" :value="$v('compliance_reminder_days', 7)" required col="col-md-6 mb-3" />
                <x-form.input name="admin_notification_email" type="email" label="Operations email" :value="$v('admin_notification_email')" col="col-md-6 mb-3" />
            </div></div>
        </div>

        {{-- ============ EMAIL (SMTP) ============ --}}
        <div class="tab-pane fade" id="tab-email">
            @php($mailer = config('mail.default'))
            <div class="smtp-status {{ $mailer === 'smtp' ? 'on' : 'off' }}">
                <span class="smtp-status-ico"><i class="bi {{ $mailer === 'smtp' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i></span>
                <div>
                    @if (! empty($s['mail_host']))
                        <strong>SMTP is active</strong>
                        <span>Emails are sent through <b>{{ $s['mail_host'] }}</b>{{ ! empty($s['mail_port']) ? ':'.$s['mail_port'] : '' }} from <b>{{ config('mail.from.address') }}</b>.</span>
                    @elseif ($mailer === 'smtp')
                        <strong>Using the server (.env) SMTP settings</strong>
                        <span>Fill in the form below to use your own mail server instead.</span>
                    @else
                        <strong>Emails are not being delivered</strong>
                        <span>The mailer is set to “{{ $mailer }}” (emails are only written to the log). Fill in your SMTP details below to send real emails.</span>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <span><i class="bi bi-envelope-at me-2 text-muted"></i>SMTP server</span>
                    <span class="ms-auto small text-muted">Quick fill:</span>
                    @foreach ([
                        'gmail' => ['Gmail', 'smtp.gmail.com', 587, 'tls'],
                        'outlook' => ['Outlook / 365', 'smtp.office365.com', 587, 'tls'],
                        'zoho' => ['Zoho', 'smtp.zoho.in', 465, 'ssl'],
                        'hostinger' => ['Hostinger', 'smtp.hostinger.com', 465, 'ssl'],
                        'godaddy' => ['GoDaddy', 'smtpout.secureserver.net', 465, 'ssl'],
                    ] as $key => [$name, $host, $port, $enc])
                        <button type="button" class="btn btn-sm smtp-preset" data-smtp-preset data-host="{{ $host }}" data-port="{{ $port }}" data-enc="{{ $enc }}">{{ $name }}</button>
                    @endforeach
                </div>
                <div class="card-body row">
                    <x-form.input name="mail_host" label="SMTP host" :value="$v('mail_host')" placeholder="smtp.gmail.com" col="col-md-6 mb-3" />
                    <x-form.input name="mail_port" type="number" label="Port" :value="$v('mail_port')" placeholder="587" col="col-md-3 mb-3" />
                    <x-form.select name="mail_encryption" label="Encryption" :options="['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)']" :value="$v('mail_encryption')" placeholder="None" col="col-md-3 mb-3" />
                    <x-form.input name="mail_username" label="Username" :value="$v('mail_username')" placeholder="you@yourdomain.com" autocomplete="off" col="col-md-6 mb-3" />
                    <div class="col-md-6 mb-3">
                        <label for="f_mail_password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="mail_password" id="f_mail_password" class="form-control @error('mail_password') is-invalid @enderror" autocomplete="new-password" placeholder="{{ ! empty($s['mail_password']) ? '•••••••• (saved)' : 'SMTP / app password' }}">
                            <button type="button" class="btn btn-light border" data-toggle-pass="f_mail_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="form-text">{{ ! empty($s['mail_password']) ? 'Saved securely (encrypted). Leave blank to keep it.' : 'For Gmail, use an App Password, not your normal password.' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-person-badge me-2 text-muted"></i>Sender</div>
                <div class="card-body row">
                    <x-form.input name="mail_from_address" type="email" label="From email" :value="$v('mail_from_address')" placeholder="noreply@yourdomain.com" help="Usually the same as the SMTP username." col="col-md-6 mb-3" />
                    <x-form.input name="mail_from_name" label="From name" :value="$v('mail_from_name')" :placeholder="$v('company_name')" help="Shown as the sender name. Leave blank to use the company name." col="col-md-6 mb-3" />
                </div>
            </div>

            <div class="card smtp-test">
                <div class="card-body d-flex flex-wrap align-items-end gap-3">
                    <div class="flex-grow-1" style="min-width: 240px">
                        <label for="test_email" class="form-label"><i class="bi bi-send me-1"></i>Send a test email</label>
                        <input type="email" name="test_email" id="test_email" form="smtp-test-form" class="form-control" value="{{ old('test_email', auth()->user()->email) }}" placeholder="you@example.com">
                        <div class="form-text">Save your SMTP settings first, then send a test to check they work.</div>
                    </div>
                    <button type="submit" form="smtp-test-form" class="btn btn-outline-primary mb-4"><i class="bi bi-send me-1"></i>Send test email</button>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-seo">
            <div class="card mb-3"><div class="card-body row">
                <x-form.input name="seo_title" label="Homepage title" :value="$v('seo_title')" col="col-12 mb-3" />
                <x-form.textarea name="seo_description" label="Default meta description" :value="$v('seo_description')" rows="2" col="col-12 mb-3" />
                <x-form.input name="seo_keywords" label="Keywords" :value="$v('seo_keywords')" col="col-md-8 mb-3" />
                <x-form.input name="google_analytics_id" label="Google Analytics ID" :value="$v('google_analytics_id')" placeholder="G-XXXXXXX" col="col-md-4 mb-3" />
            </div></div>
            <div class="card"><div class="card-header">Social links</div><div class="card-body row">
                @foreach (['facebook', 'linkedin', 'instagram', 'x', 'youtube'] as $net)
                    <x-form.input :name="'social_'.$net" type="url" :label="ucfirst($net === 'x' ? 'X (Twitter)' : $net)" :value="$v('social_'.$net)" col="col-md-6 mb-3" />
                @endforeach
            </div></div>
        </div>

        <div class="tab-pane fade" id="tab-homepage">
            <div class="card"><div class="card-body row">
                <div class="col-md-6 mb-4">
                    <label class="form-label" for="hero_image">Hero section image</label>
                    <input type="file" name="hero_image" id="hero_image" class="form-control @error('hero_image') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp"
                        data-image-preview="#heroPreview" data-max-kb="2048">
                    @error('hero_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">PNG, JPG or WebP, max 2 MB. Landscape around 1100×900 px works best.</div>
                    @if (! empty($s['hero_image']))
                        <div class="form-check mt-2">
                            <input type="checkbox" name="hero_image_reset" value="1" class="form-check-input" id="hero_image_reset"
                                data-reset-preview="#heroPreview" data-reset-src="{{ asset('images/site/hero.webp') }}">
                            <label class="form-check-label" for="hero_image_reset">Remove and use the default image</label>
                        </div>
                    @endif
                </div>
                <div class="col-md-6 mb-4">
                    <div class="img-preview" id="heroPreview">
                        <div class="img-preview-frame">
                            <img src="{{ ! empty($s['hero_image']) ? storage_asset($s['hero_image']) : asset('images/site/hero.webp') }}" alt="Hero image preview" data-preview-img>
                            <span class="img-preview-badge" data-preview-badge>{{ ! empty($s['hero_image']) ? 'Current: custom image' : 'Current: default image' }}</span>
                        </div>
                        <div class="img-preview-meta">
                            <span data-preview-info class="text-muted small"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto" data-preview-undo hidden><i class="bi bi-arrow-counterclockwise me-1"></i>Undo</button>
                        </div>
                    </div>
                </div>
                <x-form.input name="stat_customers" label="Happy customers" :value="$v('stat_customers')" col="col-md-3 mb-3" />
                <x-form.input name="stat_services" label="Services" :value="$v('stat_services')" col="col-md-3 mb-3" />
                <x-form.input name="stat_experts" label="Experts" :value="$v('stat_experts')" col="col-md-3 mb-3" />
                <x-form.input name="stat_rating" label="Rating" :value="$v('stat_rating')" col="col-md-3 mb-3" />
            </div></div>
        </div>
    </div>

    <div class="text-end mt-3"><button class="btn btn-primary btn-lg">Save Settings</button></div>
</form>

{{-- Separate form for the test email (forms can't be nested); its fields use form="smtp-test-form" --}}
<form method="POST" action="{{ route('admin.settings.test-mail') }}" id="smtp-test-form" data-no-lock>@csrf</form>
@endsection

@push('scripts')
<script>
(function () {
    // Open the tab named in the URL (#homepage) and remember it across the save redirect.
    // Panes are id="tab-homepage" so the #homepage hash never makes the browser jump-scroll.
    var tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    var saved = location.hash.slice(1) || (function () { try { return sessionStorage.getItem('settings-tab'); } catch (e) { return null; } })();
    var start = saved && document.querySelector('[data-tab-name="' + saved.replace(/[^\w-]/g, '') + '"]');
    if (start) {
        bootstrap.Tab.getOrCreateInstance(start).show();
        if (location.hash) addEventListener('load', function () { window.scrollTo(0, 0); });
    }
    tabs.forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            var name = tab.dataset.tabName;
            history.replaceState(null, '', '#' + name);
            try { sessionStorage.setItem('settings-tab', name); } catch (e) { /* ignore */ }
        });
    });
    // SMTP quick-fill buttons (Gmail, Outlook, …) fill host, port and encryption.
    document.querySelectorAll('[data-smtp-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('f_mail_host').value = btn.dataset.host;
            document.getElementById('f_mail_port').value = btn.dataset.port;
            document.getElementById('f_mail_encryption').value = btn.dataset.enc;
            document.querySelectorAll('[data-smtp-preset]').forEach(function (b) { b.classList.toggle('active', b === btn); });
            document.getElementById('f_mail_username').focus();
        });
    });
    // Show / hide the SMTP password.
    document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.togglePass);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // A field with a validation error wins: show its tab.
    var invalid = document.querySelector('.tab-pane .is-invalid');
    if (invalid) bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#' + invalid.closest('.tab-pane').id + '"]')).show();

    // Instant preview for image uploads: <input type="file" data-image-preview="#box" data-max-kb="2048">
    document.querySelectorAll('[data-image-preview]').forEach(function (input) {
        var box = document.querySelector(input.dataset.imagePreview);
        var img = box.querySelector('[data-preview-img]');
        var badge = box.querySelector('[data-preview-badge]');
        var empty = box.querySelector('[data-preview-empty]');
        var info = box.querySelector('[data-preview-info]');
        var undo = box.querySelector('[data-preview-undo]');
        var original = { src: img.getAttribute('src'), hidden: img.hidden, badge: badge ? badge.textContent : '' };
        var objectUrl = null;

        function restore() {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            img.src = original.src;
            img.hidden = original.hidden;
            if (empty) empty.hidden = !original.hidden;
            if (badge) { badge.textContent = original.badge; badge.classList.remove('is-new'); }
            box.classList.remove('is-new');
            info.textContent = '';
            info.classList.remove('text-danger');
            undo.hidden = true;
        }

        input.addEventListener('change', function () {
            var file = input.files[0];
            restore();
            if (!file) return;

            var maxKb = parseInt(input.dataset.maxKb || '0', 10);
            var problem = !/^image\/(png|jpe?g|webp)$/.test(file.type) ? 'Please choose a PNG, JPG or WebP image.'
                : (maxKb && file.size > maxKb * 1024) ? 'This image is ' + Math.round(file.size / 1024) + ' KB — the limit is ' + (maxKb >= 1024 ? (maxKb / 1024) + ' MB' : maxKb + ' KB') + '.'
                : null;
            if (problem) {
                input.value = '';
                info.textContent = problem;
                info.classList.add('text-danger');
                return;
            }

            objectUrl = URL.createObjectURL(file);
            img.src = objectUrl;
            img.hidden = false;
            if (empty) empty.hidden = true;
            if (badge) { badge.textContent = 'New — not saved yet'; badge.classList.add('is-new'); }
            box.classList.add('is-new');
            undo.hidden = false;
            img.onload = function () {
                info.textContent = file.name + ' · ' + img.naturalWidth + '×' + img.naturalHeight + ' px · ' + Math.round(file.size / 1024) + ' KB — click Save Settings to apply.';
            };

            // Picking a new image cancels "use the default image".
            var reset = document.querySelector('[data-reset-preview="' + input.dataset.imagePreview + '"]');
            if (reset) reset.checked = false;
        });

        undo.addEventListener('click', function () { input.value = ''; restore(); });

        // "Remove and use the default image" previews the default straight away.
        var reset = document.querySelector('[data-reset-preview="' + input.dataset.imagePreview + '"]');
        if (reset) reset.addEventListener('change', function () {
            input.value = '';
            restore();
            if (!reset.checked) return;
            img.src = reset.dataset.resetSrc;
            if (badge) { badge.textContent = 'Default image — after saving'; badge.classList.add('is-new'); }
            box.classList.add('is-new');
        });
    });
})();
</script>
@endpush
