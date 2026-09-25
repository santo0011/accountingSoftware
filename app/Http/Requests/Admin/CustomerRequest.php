<?php

namespace App\Http\Requests\Admin;

use App\Models\Business;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('customer') ? 'customers.edit' : 'customers.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mobile' => preg_replace('/\D/', '', (string) $this->input('mobile')) ?: null]);
    }

    public function rules(): array
    {
        $userId = $this->route('customer')?->user_id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($userId)],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
            'pan' => ['nullable', 'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
            'source' => ['nullable', 'string', 'max:50'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_type' => ['nullable', Rule::in(array_keys(Business::TYPES))],
            'send_invite' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['mobile.regex' => 'Enter a valid 10-digit mobile number.'];
    }
}
