<?php

namespace App\Http\Requests\Portal;

use App\Models\Business;
use App\Services\DocumentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates the apply wizard: the service's custom fields, business and document uploads. */
class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('service')->status;
    }

    public function rules(): array
    {
        $service = $this->route('service')->loadMissing('fields', 'documents');
        $customerId = $this->user()->customer->id;

        $rules = [
            'business_id' => ['nullable', Rule::exists('businesses', 'id')->where('customer_id', $customerId)->whereNull('deleted_at')],
            'new_business_name' => ['nullable', 'required_without:business_id', 'string', 'max:150'],
            'new_business_type' => ['nullable', Rule::in(array_keys(Business::TYPES))],
            'new_business_state' => ['nullable', 'string', 'max:100'],
            'confirm' => ['accepted'],
        ];

        foreach ($service->fields as $field) {
            $rules['fields.'.$field->name] = $field->rules();
        }

        foreach ($service->documents as $document) {
            // Documents may also be uploaded later from the application page.
            $rules['documents.'.$document->id] = DocumentService::fileRules(required: false);
        }

        return $rules;
    }

    public function attributes(): array
    {
        $service = $this->route('service');
        $attributes = ['new_business_name' => 'business name'];

        foreach ($service->fields as $field) {
            $attributes['fields.'.$field->name] = strtolower($field->label);
        }
        foreach ($service->documents as $document) {
            $attributes['documents.'.$document->id] = $document->name;
        }

        return $attributes;
    }

    public function messages(): array
    {
        return ['confirm.accepted' => 'Please confirm that the information provided is correct.'];
    }
}
