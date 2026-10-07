@extends('layouts.admin')
@section('title', (string) ($role->exists ? 'Edit Role' : 'New Role'))

@php
    // Every permission, e.g. "leads.view_all"
    $allPerms = collect($modules)->flatMap(fn ($def, $module) => collect($def['actions'])->map(fn ($a) => "$module.$a"))->values();
    $checked = old('permissions', $granted);

    // Built-in roles offered as templates ("*" means every permission)
    $presets = collect(config('rbac.roles'))->except([config('rbac.super_admin_role'), 'customer'])
        ->map(fn ($r) => ['label' => $r['label'], 'perms' => in_array('*', $r['permissions'], true) ? $allPerms->all() : $r['permissions']]);

    // Modules grouped like the sidebar menu
    $groups = [
        'CRM' => ['customers', 'leads'],
        'Operations' => ['applications', 'documents', 'tasks', 'compliance', 'support'],
        'Billing' => ['payments', 'invoices'],
        'Catalogue' => ['categories', 'services'],
        'Team' => ['staff', 'professionals', 'roles'],
        'System' => ['dashboard', 'reports', 'cms', 'settings', 'audit'],
    ];
    $icons = [
        'dashboard' => 'bi-speedometer2', 'customers' => 'bi-people', 'leads' => 'bi-person-lines-fill', 'categories' => 'bi-grid',
        'services' => 'bi-briefcase', 'applications' => 'bi-folder2-open', 'documents' => 'bi-file-earmark-check', 'payments' => 'bi-credit-card',
        'invoices' => 'bi-receipt', 'staff' => 'bi-person-badge', 'professionals' => 'bi-mortarboard', 'roles' => 'bi-shield-lock',
        'tasks' => 'bi-check2-square', 'compliance' => 'bi-calendar-check', 'support' => 'bi-headset', 'reports' => 'bi-bar-chart',
        'cms' => 'bi-window', 'settings' => 'bi-gear', 'audit' => 'bi-journal-text',
    ];
    $actionLabels = [
        'view' => 'View', 'view_all' => 'View all records', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete',
        'manage' => 'Manage', 'update' => 'Update', 'assign' => 'Assign', 'verify' => 'Verify', 'upload_final' => 'Upload final docs',
        'refund' => 'Refund', 'reply' => 'Reply',
    ];
    $label = $role->exists ? config('rbac.roles.'.$role->name.'.label', ucwords(str_replace('-', ' ', $role->name))) : null;
@endphp

@section('content')
<x-page-header :title="$role->exists ? 'Permissions: '.$label : 'New role'" :subtitle="$role->exists ? 'Choose what people with this role can see and do.' : 'Create a role, then pick exactly what it can see and do.'" :back="route('admin.roles.index')" />

<form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="role-form" novalidate>
    @csrf
    @if ($role->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-xl-5">
            <x-form.section title="Role details" text="A short key that identifies the role." icon="bi-shield-lock">
                <x-form.input name="name" label="Role key" :value="$role->name" required placeholder="e.g. senior-accountant" help="Lowercase words joined by dashes." col="col-12 mb-3" />
                <div class="col-12">
                    <div class="role-preview">
                        <span class="ic"><i class="bi bi-person-badge"></i></span>
                        <span><small>Shown to users as</small><strong data-role-label>{{ $label ?: 'New role' }}</strong></span>
                    </div>
                </div>
            </x-form.section>
        </div>
        <div class="col-xl-7">
            <x-form.section title="Start from a template" text="Copy the permissions of a built-in role, then adjust them below." icon="bi-magic" tone="violet">
                <div class="col-12">
                    <div class="role-presets">
                        @foreach ($presets as $key => $p)
                            <button type="button" class="preset" data-preset='@json($p['perms'])'><i class="bi bi-person"></i>{{ $p['label'] }}</button>
                        @endforeach
                        <button type="button" class="preset" data-preset='@json($allPerms)'><i class="bi bi-check2-all"></i>Everything</button>
                        <button type="button" class="preset clear" data-preset="[]"><i class="bi bi-x-circle"></i>Clear all</button>
                    </div>
                    <p class="small text-muted mb-0 mt-2"><i class="bi bi-info-circle me-1"></i><strong>View all records</strong> lets the role see everyone's records; without it, users only see records assigned to them.</p>
                </div>
            </x-form.section>
        </div>
    </div>

    <div class="perm-toolbar">
        <h2>Permissions</h2>
        <div class="perm-search">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Find a module, e.g. invoices" data-perm-search aria-label="Find a module">
        </div>
    </div>
    @error('permissions')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

    @foreach ($groups as $group => $keys)
        <div class="perm-group" data-perm-group>
            <h3 class="perm-group-title">{{ $group }}</h3>
            <div class="perm-grid">
                @foreach ($keys as $module)
                    @continue(! isset($modules[$module]))
                    @php($def = $modules[$module])
                    <div class="perm-card" data-perm-card data-name="{{ strtolower($def['label'].' '.$module) }}">
                        <div class="perm-card-head">
                            <span class="perm-ic"><i class="bi {{ $icons[$module] ?? 'bi-square' }}"></i></span>
                            <span class="perm-title"><strong>{{ $def['label'] }}</strong><small data-perm-count>0 of {{ count($def['actions']) }}</small></span>
                            <div class="form-check form-switch m-0" title="Turn all on / off">
                                <input class="form-check-input" type="checkbox" role="switch" data-toggle-module="{{ $module }}" aria-label="All {{ $def['label'] }} permissions">
                            </div>
                        </div>
                        <div class="perm-chips">
                            @foreach ($def['actions'] as $action)
                                @php($perm = $module.'.'.$action)
                                <label class="perm-chip">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" data-module="{{ $module }}" @checked(in_array($perm, $checked, true))>
                                    <span><i class="bi bi-check-lg"></i>{{ $actionLabels[$action] ?? ucwords(str_replace('_', ' ', $action)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
    <p class="text-muted small text-center my-4 d-none" data-perm-empty>No module matches your search.</p>

    <x-form.actions :cancel="route('admin.roles.index')" :label="$role->exists ? 'Save permissions' : 'Create role'" icon="bi-shield-check">
        <span class="perm-total me-auto"><strong data-perm-total>0</strong> of {{ $allPerms->count() }} permissions selected</span>
    </x-form.actions>
</form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    var form = document.querySelector('.role-form');
    var boxes = [].slice.call(form.querySelectorAll('input[name="permissions[]"]'));

    function refresh() {
        form.querySelectorAll('[data-perm-card]').forEach(function (card) {
            var own = [].slice.call(card.querySelectorAll('input[name="permissions[]"]'));
            var on = own.filter(function (b) { return b.checked; }).length;
            card.querySelector('[data-perm-count]').textContent = on + ' of ' + own.length;
            var sw = card.querySelector('[data-toggle-module]');
            sw.checked = on === own.length;
            sw.indeterminate = on > 0 && on < own.length;
            card.classList.toggle('is-on', on > 0);
        });
        form.querySelector('[data-perm-total]').textContent = boxes.filter(function (b) { return b.checked; }).length;
    }

    // Whole-module switch
    form.querySelectorAll('[data-toggle-module]').forEach(function (sw) {
        sw.addEventListener('change', function () {
            form.querySelectorAll('[data-module="' + sw.dataset.toggleModule + '"]').forEach(function (b) { b.checked = sw.checked; });
            refresh();
        });
    });
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });

    // Templates
    form.querySelectorAll('[data-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var set = JSON.parse(btn.dataset.preset);
            boxes.forEach(function (b) { b.checked = set.indexOf(b.value) !== -1; });
            form.querySelectorAll('[data-preset]').forEach(function (o) { o.classList.toggle('active', o === btn && !btn.classList.contains('clear')); });
            refresh();
        });
    });

    // Live "shown as" label from the role key
    var key = document.getElementById('f_name');
    var label = form.querySelector('[data-role-label]');
    if (key && !key.readOnly) key.addEventListener('input', function () {
        var words = key.value.trim().replace(/[_\s]+/g, '-').split('-').filter(Boolean);
        label.textContent = words.length ? words.map(function (w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ') : 'New role';
    });

    // Module search
    var search = form.querySelector('[data-perm-search]');
    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        var any = false;
        form.querySelectorAll('[data-perm-group]').forEach(function (group) {
            var shown = 0;
            group.querySelectorAll('[data-perm-card]').forEach(function (card) {
                var hit = !q || card.dataset.name.indexOf(q) !== -1;
                card.hidden = !hit;
                if (hit) shown++;
            });
            group.hidden = shown === 0;
            any = any || shown > 0;
        });
        form.querySelector('[data-perm-empty]').classList.toggle('d-none', any);
    });

    refresh();
})();
</script>
@endpush
