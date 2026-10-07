@extends('layouts.admin')
@section('title', (string) ($user->exists ? 'Edit Staff' : 'Add Staff'))

@section('content')
<x-page-header :title="$user->exists ? 'Edit '.$user->name : 'Add staff member'" :subtitle="$user->exists ? 'Update their details, role or password.' : 'Create a login for a new team member.'" :back="route('admin.staff.index')" />

<form method="POST" action="{{ $user->exists ? route('admin.staff.update', $user) : route('admin.staff.store') }}" class="staff-form" novalidate>
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-8">
            {{-- Account --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon"><i class="bi bi-person-vcard"></i></span>
                    <div><h2>Account details</h2><p>Their name and the email they use to sign in.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.input name="name" label="Full name" :value="$user->name" required placeholder="e.g. Priya Sharma" col="col-md-6 mb-3" />
                    <x-form.input name="email" type="email" label="Email (used to log in)" :value="$user->email" required placeholder="name@company.com" col="col-md-6 mb-3" />
                    <x-form.input name="mobile" label="Phone" :value="$user->mobile" prepend="+91" maxlength="10" placeholder="10-digit mobile" col="col-md-6 mb-md-0 mb-3" />
                </div>
            </section>

            {{-- Access --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon violet"><i class="bi bi-shield-lock"></i></span>
                    <div><h2>Role &amp; access</h2><p>The role decides which parts of the admin panel they can use.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.select name="role" label="Role" :options="$roles" :value="$user->exists ? $user->getRoleNames()->first() : 'staff'" required col="col-md-6 mb-md-0 mb-3" />
                    <x-form.select name="status" label="Status" :options="['active' => 'Active — can sign in', 'inactive' => 'Inactive — cannot sign in']" :value="$user->status" required col="col-md-6" />
                </div>
            </section>

            {{-- Job --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon teal"><i class="bi bi-briefcase"></i></span>
                    <div><h2>Job details</h2><p>Optional — shown in the staff list and on assignments.</p></div>
                </header>
                <div class="card-body row">
                    <x-form.input name="designation" label="Designation" :value="$user->staffProfile?->designation" placeholder="e.g. GST Executive" col="col-md-6 mb-3" />
                    <x-form.select name="department" label="Department" :options="array_combine(\App\Models\StaffProfile::DEPARTMENTS, \App\Models\StaffProfile::DEPARTMENTS)" :value="$user->staffProfile?->department" placeholder="Select department" col="col-md-6 mb-3" />
                    <x-form.input name="employee_code" label="Employee code" :value="$user->staffProfile?->employee_code" placeholder="e.g. EMP-012" col="col-md-6 mb-md-0 mb-3" />
                    <x-form.input name="joined_on" type="date" label="Joined on" :value="$user->staffProfile?->joined_on?->format('Y-m-d')" col="col-md-6" />
                </div>
            </section>

            {{-- Password --}}
            <section class="card form-section">
                <header class="form-section-head">
                    <span class="fs-icon amber"><i class="bi bi-key"></i></span>
                    <div>
                        <h2>{{ $user->exists ? 'Reset password' : 'Password' }}</h2>
                        <p>{{ $user->exists ? 'Leave blank to keep their current password.' : 'They can change it after signing in.' }}</p>
                    </div>
                </header>
                <div class="card-body row">
                    <div class="col-md-6 mb-md-0 mb-3 pw-field">
                        <x-form.input name="password" type="password" label="Password" :required="! $user->exists" autocomplete="new-password" col="" />
                        <button type="button" class="pw-toggle" data-toggle-pass="f_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="col-md-6 pw-field">
                        <x-form.input name="password_confirmation" type="password" label="Confirm password" :required="! $user->exists" autocomplete="new-password" col="" />
                        <button type="button" class="pw-toggle" data-toggle-pass="f_password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </section>
        </div>

        {{-- Live preview --}}
        <div class="col-xl-4">
            <aside class="staff-preview" aria-label="Preview">
                <div class="sp-cover"></div>
                <div class="sp-body">
                    <span class="sp-avatar" data-pv-initials>{{ $user->exists ? $user->initials() : '?' }}</span>
                    <strong class="sp-name" data-pv-name data-empty="New staff member">{{ $user->name ?: 'New staff member' }}</strong>
                    <span class="sp-email" data-pv-email data-empty="email@company.com">{{ $user->email ?: 'email@company.com' }}</span>
                    <div class="sp-tags">
                        <span class="badge badge-soft-primary" data-pv-role></span>
                        <span class="status-dot on" data-pv-status>Active</span>
                    </div>
                    <dl class="sp-meta">
                        <div><dt>Designation</dt><dd data-pv-designation data-empty="—">{{ $user->staffProfile?->designation ?: '—' }}</dd></div>
                        <div><dt>Department</dt><dd data-pv-department data-empty="—">{{ $user->staffProfile?->department ?: '—' }}</dd></div>
                    </dl>
                </div>
                <p class="sp-note"><i class="bi bi-info-circle"></i> They sign in at the staff login with their email and this password.</p>
            </aside>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $user->exists ? 'Save changes' : 'Add staff member' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    var form = document.querySelector('.staff-form');
    var $ = function (sel) { return form.querySelector(sel); };
    var text = function (el, value) { el.textContent = value && value.trim() ? value.trim() : el.dataset.empty; };
    var initials = function (name) {
        var parts = name.trim().split(/\s+/).filter(Boolean);
        return parts.length ? (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase() : '?';
    };
    var selected = function (sel) { return sel.value ? sel.options[sel.selectedIndex].text : ''; };

    function update() {
        var name = $('#f_name').value;
        text($('[data-pv-name]'), name);
        $('[data-pv-initials]').textContent = initials(name);
        text($('[data-pv-email]'), $('#f_email').value);
        text($('[data-pv-designation]'), $('#f_designation').value);
        text($('[data-pv-department]'), selected($('#f_department')));
        $('[data-pv-role]').textContent = selected($('#f_role')) || 'No role';
        var active = $('#f_status').value === 'active';
        var status = $('[data-pv-status]');
        status.textContent = active ? 'Active' : 'Inactive';
        status.className = 'status-dot ' + (active ? 'on' : 'off');
    }
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();

    // Show / hide password
    form.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.togglePass);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
})();
</script>
@endpush
