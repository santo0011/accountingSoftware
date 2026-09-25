@php($be = $errors->business)
@foreach ([
    ['name', 'Business name', 'col-md-6', true],
    ['gstin', 'GSTIN', 'col-md-3', false],
    ['pan', 'Business PAN', 'col-md-3', false],
    ['registration_no', 'CIN / LLPIN / Udyam no.', 'col-md-4', false],
    ['city', 'City', 'col-md-4', false],
    ['state', 'State', 'col-md-4', false],
    ['address', 'Registered address', 'col-md-9', false],
    ['pincode', 'PIN code', 'col-md-3', false],
] as [$field, $label, $col, $required])
    <div class="{{ $col }} mb-3">
        <label class="form-label {{ $required ? 'required' : '' }}">{{ $label }}</label>
        <input type="text" name="{{ $field }}" value="{{ $b ? old($field, $b->$field) : old($field) }}" class="form-control {{ $be->has($field) ? 'is-invalid' : '' }}" @required($required)>
        @if ($be->has($field))<div class="invalid-feedback">{{ $be->first($field) }}</div>@endif
    </div>
@endforeach
<div class="col-md-6 mb-3">
    <label class="form-label">Business type</label>
    <select name="business_type" class="form-select">
        <option value="">Select</option>
        @foreach (\App\Models\Business::TYPES as $value => $label)
            <option value="{{ $value }}" @selected(($b?->business_type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-6 mb-3">
    <label class="form-label">Date of incorporation</label>
    <input type="date" name="incorporation_date" value="{{ $b?->incorporation_date?->format('Y-m-d') }}" class="form-control" max="{{ now()->format('Y-m-d') }}">
</div>
