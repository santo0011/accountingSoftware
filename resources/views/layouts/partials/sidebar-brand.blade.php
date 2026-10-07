{{-- Sidebar header shared by admin, staff and customer panels.
     With an uploaded logo: the full logo on a white card (it already carries the name), panel label underneath.
     Without one: icon tile + company name. Collapsed: a square mark (favicon, else the left part of the logo).
     Expects: $href (dashboard URL), $subtitle ("Admin Panel", "Staff Panel", "Customer Portal"). --}}
@php
    $company = setting('company_name', config('app.name'));
    $logo = setting('logo') ? storage_asset(setting('logo')) : null;
    $mark = setting('favicon') ? storage_asset(setting('favicon')) : null;
@endphp
<div class="sidebar-brand sb-head {{ $logo ? 'has-logo' : '' }}">
    <a href="{{ $href }}" class="sb-home" aria-label="{{ $company }} dashboard">
        @if ($logo)
            <span class="sb-logo-card"><img src="{{ $logo }}" alt="{{ $company }}"></span>
            <span class="sb-mark {{ $mark ? '' : 'from-logo' }}" aria-hidden="true"><img src="{{ $mark ?? $logo }}" alt=""></span>
        @else
            <span class="sb-logo"><i class="bi bi-bar-chart-steps"></i></span>
            <span class="sb-title"><strong>{{ $company }}</strong></span>
        @endif
    </a>
    <button type="button" class="sb-collapse" data-sidebar-toggle aria-label="Collapse or expand the sidebar" title="Collapse sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>
    <span class="sb-panel"><span class="dot"></span>{{ $subtitle }}</span>
</div>
