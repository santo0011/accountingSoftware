@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'helpOnError' => false, 'prepend' => null, 'col' => null])
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $current = $type === 'password' || $type === 'file' ? null : old($key, $value);
    $id = $attributes->get('id', 'f_'.str_replace(['[', ']', '.'], '_', $name));
@endphp
<div class="{{ $col ?? 'mb-3' }}">
    @if ($label)<label for="{{ $id }}" class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>@endif
    @if ($prepend)<div class="input-group has-validation"><span class="input-group-text">{!! $prepend !!}</span>@endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $current }}" @required($required)
        {{ $attributes->except('id')->merge(['class' => 'form-control'.($errors->has($key) ? ' is-invalid' : '')]) }}>
    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($prepend)</div>@endif
    {{-- helpOnError: the hint only appears once this field has a validation error --}}
    @if ($help && (! $helpOnError || $errors->has($key)))<div class="form-text">{{ $help }}</div>@endif
</div>
