<?php

namespace App\Http\Requests\Portal;

use App\Models\Business;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessRequest extends FormRequest
{
    protected $errorBag = 'business';

    public function authorize(): bool
    {
        $business = $this->route('business');

        return ! $business || $business->customer_id === $this->user()->customer?->id;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gstin' => $this->filled('gstin') ? strtoupper(trim($this->input('gstin'))) : null,
            'pan' => $this->filled('pan') ? strtoupper(trim($this->input('pan'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'business_type' => ['nullable', Rule::in(array_keys(Business::TYPES))],
            'gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'pan' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'registration_no' => ['nullable', 'string', 'max:50'],
            'incorporation_date' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return ['gstin.regex' => 'Enter a valid 15-character GSTIN.', 'pan.regex' => 'Enter a valid PAN.'];
    }
}
