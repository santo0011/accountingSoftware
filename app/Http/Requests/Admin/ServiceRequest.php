<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Models\ServiceField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('services.manage');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('name'))]);

        // Drop completely empty repeater rows.
        foreach (['documents' => 'name', 'fields' => 'label', 'faqs' => 'question', 'steps' => 'title'] as $group => $key) {
            $this->merge([$group => array_values(array_filter((array) $this->input($group, []), fn ($row) => filled($row[$key] ?? null)))]);
        }
    }

    public function rules(): array
    {
        $service = $this->route('service');

        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('services', 'slug')->ignore($service?->id)],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^bi-[a-z0-9-]+$/'],
            'short_description' => ['required', 'string', 'max:500'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'full_description' => ['nullable', 'string', 'max:20000'],
            'who_needs_text' => ['nullable', 'string', 'max:3000'],
            'benefits_text' => ['nullable', 'string', 'max:3000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'gst_rate' => ['required', 'numeric', Rule::in([0, 5, 12, 18, 28])],
            'sac_code' => ['nullable', 'string', 'max:10'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'billing_type' => ['required', Rule::in(['one_time', 'recurring'])],
            'recurring_interval' => ['nullable', 'required_if:billing_type,recurring', Rule::in(array_keys(Service::INTERVALS))],
            'compliance_type_id' => ['nullable', 'exists:compliance_types,id'],
            'is_featured' => ['boolean'],
            'status' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],

            'documents' => ['array', 'max:30'],
            'documents.*.id' => ['nullable', 'integer'],
            'documents.*.name' => ['required', 'string', 'max:200'],
            'documents.*.description' => ['nullable', 'string', 'max:255'],
            'documents.*.is_mandatory' => ['nullable', 'boolean'],

            'fields' => ['array', 'max:30'],
            'fields.*.id' => ['nullable', 'integer'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.name' => ['nullable', 'string', 'max:60'],
            'fields.*.type' => ['required', Rule::in(array_keys(ServiceField::TYPES))],
            'fields.*.options' => ['nullable', 'string', 'max:1000'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:150'],
            'fields.*.is_required' => ['nullable', 'boolean'],

            'faqs' => ['array', 'max:30'],
            'faqs.*.id' => ['nullable', 'integer'],
            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string', 'max:3000'],

            'steps' => ['array', 'max:20'],
            'steps.*.id' => ['nullable', 'integer'],
            'steps.*.title' => ['required', 'string', 'max:150'],
            'steps.*.description' => ['nullable', 'string', 'max:1000'],
            'steps.*.duration' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return ['icon.regex' => 'Use a Bootstrap Icons class, e.g. bi-building.'];
    }
}
