@extends('layouts.admin')
@section('title', (string) ($lead->exists ? 'Edit Lead' : 'Add Lead'))

@section('content')
<x-page-header :title="$lead->exists ? 'Edit lead' : 'Add lead'" :subtitle="$lead->exists ? 'Update the enquiry, owner or follow-up date.' : 'Capture a new enquiry from a call, walk-in or referral.'" :back="$lead->exists ? route('admin.leads.show', $lead) : route('admin.leads.index')" />

<form method="POST" action="{{ $lead->exists ? route('admin.leads.update', $lead) : route('admin.leads.store') }}" class="lead-form" novalidate>
    @csrf
    @if ($lead->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            {{-- Contact --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon"><i class="bi bi-person-lines-fill"></i></span>
                    <div><h2>Contact person</h2><p>Who enquired and how to reach them.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.input name="name" label="Name" :value="$lead->name" required placeholder="e.g. Rohan Verma" col="col-md-6 mb-3" />
                    <x-form.input name="company" label="Company" :value="$lead->company" placeholder="Optional — e.g. Verma Traders" col="col-md-6 mb-3" />
                    <x-form.input name="phone" label="Phone" :value="$lead->phone" prepend="+91" maxlength="10" placeholder="10-digit mobile" col="col-md-4 mb-md-0 mb-3" />
                    <x-form.input name="email" type="email" label="Email" :value="$lead->email" placeholder="name@example.com" col="col-md-4 mb-md-0 mb-3" />
                    <x-form.input name="city" label="City" :value="$lead->city" placeholder="e.g. Kolkata" col="col-md-4" />
                </div>
            </section>

            {{-- Enquiry --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon violet"><i class="bi bi-chat-square-text"></i></span>
                    <div><h2>Enquiry</h2><p>What they need and where the lead came from.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.select name="service_id" label="Service interested in" :options="$services" :value="$lead->service_id" placeholder="Not sure yet" col="col-md-6 mb-3" />
                    <x-form.select name="source" label="Source" :options="$sources" :value="$lead->source" required :col="$lead->exists ? 'col-md-3 mb-3' : 'col-md-6 mb-3'" />
                    @if ($lead->exists)
                        <x-form.select name="status" label="Status" :options="$statuses" :value="$lead->status" col="col-md-3 mb-3" />
                    @endif
                    <x-form.textarea name="message" label="Enquiry / requirement" :value="$lead->message" rows="3" placeholder="What did they ask for? Budget, timeline, documents they have…" col="col-12" />
                </div>
            </section>

            {{-- Follow-up --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon teal"><i class="bi bi-calendar-check"></i></span>
                    <div><h2>Follow-up &amp; notes</h2><p>Who owns this lead and when to contact them next.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.select name="assigned_to" label="Assigned to" :options="$staff" :value="$lead->assigned_to" placeholder="Unassigned" col="col-md-6 mb-3" />
                    <div class="col-md-6 mb-3">
                        <x-form.input name="next_followup_at" type="date" label="Next follow-up" :value="$lead->next_followup_at?->format('Y-m-d')" col="" />
                        <div class="quick-dates" data-for="f_next_followup_at" role="group" aria-label="Quick follow-up dates">
                            <button type="button" data-days="0">Today</button>
                            <button type="button" data-days="1">Tomorrow</button>
                            <button type="button" data-days="3">In 3 days</button>
                            <button type="button" data-days="7">Next week</button>
                        </div>
                    </div>
                    <x-form.textarea name="notes" label="Internal notes" :value="$lead->notes" rows="3" placeholder="Only visible to your team — call outcomes, objections, next steps." col="col-12" />
                </div>
            </section>
        </div>

        {{-- Live preview --}}
        <div class="col-xl-4">
            <aside class="staff-preview lead-preview" aria-label="Preview">
                <div class="sp-cover"></div>
                <div class="sp-body">
                    <span class="sp-avatar" data-pv-initials>{{ $lead->name ? collect(explode(' ', trim($lead->name)))->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') : '?' }}</span>
                    <strong class="sp-name" data-pv-name data-empty="New lead">{{ $lead->name ?: 'New lead' }}</strong>
                    <span class="sp-email" data-pv-company data-empty="Individual">{{ $lead->company ?: 'Individual' }}</span>
                    <div class="sp-tags">
                        <span class="badge badge-soft-primary" data-pv-source></span>
                        @if ($lead->exists)<span class="badge badge-soft-secondary" data-pv-status></span>@endif
                    </div>
                    <dl class="sp-meta">
                        <div><dt>Phone</dt><dd data-pv-phone data-empty="—">{{ $lead->phone ?: '—' }}</dd></div>
                        <div><dt>City</dt><dd data-pv-city data-empty="—">{{ $lead->city ?: '—' }}</dd></div>
                        <div class="full"><dt>Interested in</dt><dd data-pv-service data-empty="Not sure yet">{{ $lead->service?->name ?: 'Not sure yet' }}</dd></div>
                        <div><dt>Assigned to</dt><dd data-pv-assigned data-empty="Unassigned">Unassigned</dd></div>
                        <div><dt>Follow-up</dt><dd data-pv-followup data-empty="Not set">Not set</dd></div>
                    </dl>
                </div>
                <p class="sp-note"><i class="bi bi-lightbulb"></i> Set a follow-up date so the lead shows up in your reminders and never goes cold.</p>
            </aside>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ $lead->exists ? route('admin.leads.show', $lead) : route('admin.leads.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $lead->exists ? 'Save changes' : 'Create lead' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    var form = document.querySelector('.lead-form');
    var $ = function (sel) { return form.querySelector(sel); };
    var text = function (el, value) { if (el) el.textContent = value && value.trim() ? value.trim() : el.dataset.empty; };
    var selected = function (sel) { return sel && sel.value ? sel.options[sel.selectedIndex].text : ''; };
    var initials = function (name) {
        var parts = name.trim().split(/\s+/).filter(Boolean);
        return parts.length ? (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase() : '?';
    };
    var fmtDate = function (v) {
        if (!v) return '';
        var d = new Date(v + 'T00:00:00');
        return isNaN(d) ? v : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    function update() {
        var name = $('#f_name').value;
        text($('[data-pv-name]'), name);
        $('[data-pv-initials]').textContent = initials(name);
        text($('[data-pv-company]'), $('#f_company').value);
        text($('[data-pv-phone]'), $('#f_phone').value);
        text($('[data-pv-city]'), $('#f_city').value);
        text($('[data-pv-service]'), selected($('#f_service_id')));
        text($('[data-pv-assigned]'), selected($('#f_assigned_to')));
        $('[data-pv-source]').textContent = selected($('#f_source')) || 'No source';
        if ($('[data-pv-status]')) $('[data-pv-status]').textContent = selected($('#f_status'));
        var due = $('#f_next_followup_at').value;
        var dd = $('[data-pv-followup]');
        text(dd, fmtDate(due));
        dd.classList.toggle('is-overdue', !!due && new Date(due + 'T23:59:59') < new Date());
    }


    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
@endpush
