{{--
    Image upload with instant preview, drag & drop, size/type check and undo (behaviour in panel.js → [data-img-up]).
    current:  URL of the saved image (null if none)      default: URL shown when nothing is saved / after "remove"
    ratio:    CSS aspect-ratio of the preview box        fit: cover (photos) or contain (logos, icons)
    remove:   name of a checkbox the controller reads to drop the saved image, e.g. remove_image
--}}
@props([
    'name', 'label' => null, 'current' => null, 'default' => null, 'help' => null, 'maxKb' => 2048,
    'accept' => null, 'ratio' => '16 / 9', 'fit' => 'cover',
    'remove' => null, 'removeLabel' => 'Remove and use the default image', 'col' => null,
])
@php
    $id = $attributes->get('id', 'f_'.str_replace(['[', ']', '.'], '_', $name));
    $shown = $current ?: $default;
    $state = $current ? 'Current image' : ($default ? 'Default image' : null);
    $maxLabel = $maxKb >= 1024 ? rtrim(rtrim(number_format($maxKb / 1024, 1), '0'), '.').' MB' : $maxKb.' KB';
    $accept ??= \App\Support\FileTypes::accept(\App\Support\FileTypes::WEB_IMAGES);
    // Short label from the extensions: "JPG, PNG, WEBP, GIF & more"
    $exts = collect(explode(',', $accept))->filter(fn ($a) => str_starts_with($a, '.'))->map(fn ($a) => strtoupper(ltrim($a, '.')))
        ->reject(fn ($e) => in_array($e, ['JPEG', 'PJPEG', 'PJP'], true))->values();
    $types = $exts->take(4)->implode(', ').($exts->count() > 4 ? ' & more' : '');
@endphp
<div class="{{ $col ?? 'mb-3' }}">
    @if ($label)<label for="{{ $id }}" class="form-label">{{ $label }}</label>@endif
    <div class="img-up img-up-{{ $fit }} {{ $errors->has($name) ? 'is-invalid' : '' }}" data-img-up data-max-kb="{{ $maxKb }}" data-default="{{ $default }}">
        <div class="img-up-drop" style="--ratio: {{ $ratio }}" tabindex="0" role="button" aria-controls="{{ $id }}" aria-label="{{ $label ? 'Choose '.strtolower($label) : 'Choose image' }}" data-drop>
            <img src="{{ $shown }}" alt="" data-img @if (! $shown) hidden @endif>
            <span class="img-up-empty" data-empty @if ($shown) hidden @endif>
                <span class="img-up-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                <strong>Click to upload <span class="d-none d-sm-inline">or drag &amp; drop</span></strong>
                <small>{{ $types }} · up to {{ $maxLabel }}</small>
            </span>
            <span class="img-up-badge" data-badge @if (! $state) hidden @endif>{{ $state }}</span>
            <span class="img-up-change" aria-hidden="true"><i class="bi bi-arrow-repeat"></i> Change image</span>
        </div>
        <input type="file" name="{{ $name }}" id="{{ $id }}" accept="{{ $accept }}" class="visually-hidden" tabindex="-1" data-input {{ $attributes->except('id') }}>

        <div class="img-up-bar">
            <span class="img-up-info" data-info data-help="{{ $help ?? $types.', up to '.$maxLabel.'.' }}">{{ $help ?? $types.', up to '.$maxLabel.'.' }}</span>
            <button type="button" class="img-up-btn" data-undo hidden><i class="bi bi-arrow-counterclockwise"></i> Undo</button>
            <button type="button" class="img-up-btn primary" data-pick><i class="bi bi-upload"></i> {{ $shown ? 'Replace' : 'Upload' }}</button>
        </div>
        @if ($remove && $current)
            <div class="form-check img-up-remove">
                <input type="checkbox" name="{{ $remove }}" value="1" class="form-check-input" id="{{ $id }}_remove" data-remove @checked(old($remove))>
                <label class="form-check-label" for="{{ $id }}_remove">{{ $removeLabel }}</label>
            </div>
        @endif
    </div>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
