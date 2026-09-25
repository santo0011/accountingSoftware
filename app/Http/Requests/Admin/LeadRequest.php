<?php

namespace App\Http\Requests\Admin;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('lead') ? 'leads.edit' : 'leads.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => preg_replace('/\D/', '', (string) $this->input('phone')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['required_without:email', 'nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'company' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'service_id' => ['nullable', 'exists:services,id'],
            'source' => ['required', Rule::in(array_keys(Lead::SOURCES))],
            'status' => ['nullable', new Enum(LeadStatus::class)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->whereIn('user_type', ['staff', 'professional'])],
            'message' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'next_followup_at' => ['nullable', 'date'],
        ];
    }
}
