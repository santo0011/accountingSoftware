@props(['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 3, 'help' => null, 'col' => null])
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', 'f_'.str_replace(['[', ']', '.'], '_', $name));
@endphp
<div class="{{ $col ?? 'mb-3' }}">
    @if ($label)<label for="{{ $id }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>@endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" @required($required)
        {{ $attributes->except('id')->merge(['class' => 'form-control'.($errors->has($key) ? ' is-invalid' : '')]) }}>{{ old($key, $value) }}</textarea>
    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($help)<div class="form-text">{{ $help }}</div>@endif
</div>
