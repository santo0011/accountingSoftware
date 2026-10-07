{{-- Sticky action bar at the bottom of a form: <x-form.actions :cancel="route('admin.tasks.index')" label="Save task" /> --}}
@props(['cancel' => null, 'label' => 'Save', 'icon' => 'bi-check2'])
<div {{ $attributes->merge(['class' => 'form-actions']) }}>
    {{ $slot }}
    @if ($cancel)<a href="{{ $cancel }}" class="btn btn-light">Cancel</a>@endif
    <button class="btn btn-primary"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</button>
</div>
