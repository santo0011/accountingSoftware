<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => preg_replace('/\D/', '', (string) $this->input('phone'))]);
    }

    public function rules(): array
    {
        $callback = $this->routeIs('site.callback.store');

        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => [$callback ? 'nullable' : 'required', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'message' => [$callback ? 'nullable' : 'required', 'string', 'max:2000'],
            'website' => ['prohibited'], // honeypot — bots fill every field
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Enter a valid 10-digit mobile number.'];
    }
}
