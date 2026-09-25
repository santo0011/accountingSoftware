@props(['name', 'label', 'checked' => false, 'switch' => true, 'col' => null, 'help' => null])
@php($id = 'f_'.str_replace(['[', ']', '.'], '_', $name))
<div class="{{ $col ?? 'mb-3' }}">
    <input type="hidden" name="{{ $name }}" value="0">
    <div class="form-check {{ $switch ? 'form-switch' : '' }}">
        <input class="form-check-input" type="checkbox" role="{{ $switch ? 'switch' : 'checkbox' }}" name="{{ $name }}" id="{{ $id }}" value="1" @checked(old($name, $checked))>
        <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
    </div>
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
