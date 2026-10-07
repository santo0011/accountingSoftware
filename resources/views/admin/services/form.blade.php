@extends('layouts.admin')
@section('title', (string) ($service->exists ? 'Edit Service' : 'Add Service'))

@php
    // Repeater rows: old input after a validation error, else saved rows.
    $rows = fn (string $key, $saved) => old($key, $saved);
    $documents = $rows('documents', $service->exists ? $service->documents->map->only(['id', 'name', 'description', 'is_mandatory'])->all() : []);
    $fields = $rows('fields', $service->exists ? $service->fields->map(fn ($f) => ['id' => $f->id, 'label' => $f->label, 'name' => $f->name, 'type' => $f->type, 'options' => implode(', ', $f->options ?? []), 'placeholder' => $f->placeholder, 'is_required' => $f->is_required])->all() : []);
    $faqs = $rows('faqs', $service->exists ? $service->faqs->map->only(['id', 'question', 'answer'])->all() : []);
    $steps = $rows('steps', $service->exists ? $service->steps->map->only(['id', 'title', 'description', 'duration'])->all() : []);
@endphp

@section('content')
<x-page-header :title="$service->exists ? 'Edit: '.$service->name : 'Add service'" :back="route('admin.services.index')">
    @if ($service->exists)<a href="{{ route('site.services.show', $service->slug) }}" target="_blank" class="btn btn-light"><i class="bi bi-box-arrow-up-right me-1"></i>View on site</a>@endif
</x-page-header>

<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($service->exists) @method('PUT') @endif

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#t-basic">Basics &amp; pricing</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#t-content">Page content</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#t-docs">Documents <span class="badge badge-soft-secondary">{{ count($documents) }}</span></button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#t-fields">Application fields <span class="badge badge-soft-secondary">{{ count($fields) }}</span></button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#t-process">Process &amp; FAQ</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#t-seo">SEO</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="t-basic">
            <div class="card"><div class="card-body row">
                <x-form.input name="name" label="Service name" :value="$service->name" required col="col-md-6 mb-3" />
                <x-form.input name="slug" label="URL slug" :value="$service->slug" help="/services/your-slug — leave blank to generate." col="col-md-6 mb-3" />
                <x-form.select name="service_category_id" label="Category" :options="$categories" :value="$service->service_category_id" placeholder="Select" required col="col-md-6 mb-3" />
                <x-form.input name="icon" label="Icon" :value="$service->icon" placeholder="bi-building-check" col="col-md-3 mb-3" />
                <x-form.input name="sort_order" type="number" label="Sort order" :value="$service->sort_order ?? 0" col="col-md-3 mb-3" />
                <x-form.textarea name="short_description" label="Short description" :value="$service->short_description" rows="2" required help="Shown at the top of the service page (max 500 characters)." col="col-12 mb-3" />
                <x-form.input name="tagline" label="Card line" :value="$service->tagline" maxlength="120" help="One short sentence shown on service cards, e.g. “Get your GST registration done easily.”" col="col-md-7 mb-3" />
                <x-form.image name="image" label="Card image" :current="$service->image ? $service->imageUrl() : null" :default="asset('images/site/placeholder.webp')"
                    ratio="3 / 2" help="JPG, PNG, WebP, GIF or JFIF, landscape (3:2), up to 2 MB." remove="remove_image" remove-label="Use the default image" col="col-md-5 mb-3" />
                <x-form.input name="price" type="number" step="0.01" label="Price (₹, excl. GST)" :value="$service->price" required col="col-md-3 mb-3" />
                <x-form.input name="discount_price" type="number" step="0.01" label="Discount price (₹)" :value="$service->discount_price" help="Optional offer price." col="col-md-3 mb-3" />
                <x-form.select name="gst_rate" label="GST rate" :options="[0 => '0%', 5 => '5%', 12 => '12%', 18 => '18%', 28 => '28%']" :value="(int) $service->gst_rate" col="col-md-3 mb-3" />
                <x-form.input name="sac_code" label="SAC code" :value="$service->sac_code" col="col-md-3 mb-3" />
                <x-form.input name="processing_time" label="Processing time" :value="$service->processing_time" placeholder="e.g. 7–12 working days" col="col-md-6 mb-3" />
                <x-form.select name="billing_type" label="Service type" :options="['one_time' => 'One-time service', 'recurring' => 'Recurring service']" :value="$service->billing_type" col="col-md-6 mb-3"
                    onchange="document.getElementById('recurringBox').classList.toggle('d-none', this.value !== 'recurring')" />
                <div id="recurringBox" class="col-12 {{ old('billing_type', $service->billing_type) === 'recurring' ? '' : 'd-none' }}">
                    <div class="row bg-soft rounded p-2 mb-3 mx-0">
                        <x-form.select name="recurring_interval" label="Billing interval" :options="\App\Models\Service::INTERVALS" :value="$service->recurring_interval" col="col-md-6 my-2" />
                        <x-form.select name="compliance_type_id" label="Creates compliance calendar entries for" :options="$complianceTypes" :value="$service->compliance_type_id" placeholder="None" col="col-md-6 my-2" />
                    </div>
                </div>
                <x-form.check name="status" label="Active (visible on website)" :checked="$service->status" col="col-md-4" />
                <x-form.check name="is_featured" label="Featured on homepage" :checked="$service->is_featured" col="col-md-4" />
            </div></div>
        </div>

        <div class="tab-pane fade" id="t-content">
            <div class="card"><div class="card-body">
                <x-form.textarea name="full_description" label="Overview (HTML allowed: p, strong, ul, li, h2, h3, a)" :value="$service->full_description" rows="8" />
                <div class="row">
                    <x-form.textarea name="who_needs_text" label="Who needs this service? (one per line)" :value="implode(PHP_EOL, $service->who_needs ?? [])" rows="5" col="col-md-6 mb-3" />
                    <x-form.textarea name="benefits_text" label="Benefits (one per line)" :value="implode(PHP_EOL, $service->benefits ?? [])" rows="5" col="col-md-6 mb-3" />
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="t-docs">
            <div class="card"><div class="card-body">
                <p class="small text-muted">Documents customers upload when applying. Mandatory ones are highlighted in the apply form.</p>
                <div data-repeater="documents">
                    @foreach ($documents as $i => $d)
                        <div class="row g-2 align-items-center mb-2 rep-row">
                            <input type="hidden" name="documents[{{ $i }}][id]" value="{{ $d['id'] ?? '' }}">
                            <div class="col-md-5"><input type="text" name="documents[{{ $i }}][name]" value="{{ $d['name'] ?? '' }}" class="form-control form-control-sm" placeholder="Document name"></div>
                            <div class="col-md-4"><input type="text" name="documents[{{ $i }}][description]" value="{{ $d['description'] ?? '' }}" class="form-control form-control-sm" placeholder="Hint (optional)"></div>
                            <div class="col-md-2"><div class="form-check"><input type="hidden" name="documents[{{ $i }}][is_mandatory]" value="0"><input type="checkbox" class="form-check-input" name="documents[{{ $i }}][is_mandatory]" value="1" @checked(! empty($d['is_mandatory']))><label class="form-check-label small">Required</label></div></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    @endforeach
                    <template>
                        <div class="row g-2 align-items-center mb-2 rep-row">
                            <input type="hidden" name="documents[__i__][id]" value="">
                            <div class="col-md-5"><input type="text" name="documents[__i__][name]" class="form-control form-control-sm" placeholder="Document name"></div>
                            <div class="col-md-4"><input type="text" name="documents[__i__][description]" class="form-control form-control-sm" placeholder="Hint (optional)"></div>
                            <div class="col-md-2"><div class="form-check"><input type="hidden" name="documents[__i__][is_mandatory]" value="0"><input type="checkbox" class="form-check-input" name="documents[__i__][is_mandatory]" value="1" checked><label class="form-check-label small">Required</label></div></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i>Add document</button>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="t-fields">
            <div class="card"><div class="card-body">
                <p class="small text-muted">Questions asked in the application form. For dropdowns, enter options separated by commas.</p>
                <div data-repeater="fields">
                    @foreach ($fields as $i => $f)
                        <div class="row g-2 align-items-center mb-2 rep-row">
                            <input type="hidden" name="fields[{{ $i }}][id]" value="{{ $f['id'] ?? '' }}">
                            <input type="hidden" name="fields[{{ $i }}][name]" value="{{ $f['name'] ?? '' }}">
                            <div class="col-md-3"><input type="text" name="fields[{{ $i }}][label]" value="{{ $f['label'] ?? '' }}" class="form-control form-control-sm" placeholder="Question / label"></div>
                            <div class="col-md-2"><select name="fields[{{ $i }}][type]" class="form-select form-select-sm">@foreach (\App\Models\ServiceField::TYPES as $v => $l)<option value="{{ $v }}" @selected(($f['type'] ?? 'text') === $v)>{{ $l }}</option>@endforeach</select></div>
                            <div class="col-md-3"><input type="text" name="fields[{{ $i }}][options]" value="{{ $f['options'] ?? '' }}" class="form-control form-control-sm" placeholder="Options (for dropdown)"></div>
                            <div class="col-md-2"><input type="text" name="fields[{{ $i }}][placeholder]" value="{{ $f['placeholder'] ?? '' }}" class="form-control form-control-sm" placeholder="Placeholder"></div>
                            <div class="col-md-1"><div class="form-check"><input type="hidden" name="fields[{{ $i }}][is_required]" value="0"><input type="checkbox" class="form-check-input" name="fields[{{ $i }}][is_required]" value="1" @checked(! empty($f['is_required']))><label class="form-check-label small">Req.</label></div></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    @endforeach
                    <template>
                        <div class="row g-2 align-items-center mb-2 rep-row">
                            <input type="hidden" name="fields[__i__][id]" value=""><input type="hidden" name="fields[__i__][name]" value="">
                            <div class="col-md-3"><input type="text" name="fields[__i__][label]" class="form-control form-control-sm" placeholder="Question / label"></div>
                            <div class="col-md-2"><select name="fields[__i__][type]" class="form-select form-select-sm">@foreach (\App\Models\ServiceField::TYPES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                            <div class="col-md-3"><input type="text" name="fields[__i__][options]" class="form-control form-control-sm" placeholder="Options (for dropdown)"></div>
                            <div class="col-md-2"><input type="text" name="fields[__i__][placeholder]" class="form-control form-control-sm" placeholder="Placeholder"></div>
                            <div class="col-md-1"><div class="form-check"><input type="hidden" name="fields[__i__][is_required]" value="0"><input type="checkbox" class="form-check-input" name="fields[__i__][is_required]" value="1"><label class="form-check-label small">Req.</label></div></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i>Add field</button>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="t-process">
            <div class="card mb-3"><div class="card-header">Process steps</div><div class="card-body">
                <div data-repeater="steps">
                    @foreach ($steps as $i => $st)
                        <div class="row g-2 mb-2 rep-row">
                            <input type="hidden" name="steps[{{ $i }}][id]" value="{{ $st['id'] ?? '' }}">
                            <div class="col-md-3"><input type="text" name="steps[{{ $i }}][title]" value="{{ $st['title'] ?? '' }}" class="form-control form-control-sm" placeholder="Step title"></div>
                            <div class="col-md-6"><input type="text" name="steps[{{ $i }}][description]" value="{{ $st['description'] ?? '' }}" class="form-control form-control-sm" placeholder="Description"></div>
                            <div class="col-md-2"><input type="text" name="steps[{{ $i }}][duration]" value="{{ $st['duration'] ?? '' }}" class="form-control form-control-sm" placeholder="Duration"></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    @endforeach
                    <template>
                        <div class="row g-2 mb-2 rep-row">
                            <input type="hidden" name="steps[__i__][id]" value="">
                            <div class="col-md-3"><input type="text" name="steps[__i__][title]" class="form-control form-control-sm" placeholder="Step title"></div>
                            <div class="col-md-6"><input type="text" name="steps[__i__][description]" class="form-control form-control-sm" placeholder="Description"></div>
                            <div class="col-md-2"><input type="text" name="steps[__i__][duration]" class="form-control form-control-sm" placeholder="Duration"></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i>Add step</button>
                </div>
            </div></div>
            <div class="card"><div class="card-header">FAQs</div><div class="card-body">
                <div data-repeater="faqs">
                    @foreach ($faqs as $i => $fq)
                        <div class="row g-2 mb-2 rep-row">
                            <input type="hidden" name="faqs[{{ $i }}][id]" value="{{ $fq['id'] ?? '' }}">
                            <div class="col-md-4"><input type="text" name="faqs[{{ $i }}][question]" value="{{ $fq['question'] ?? '' }}" class="form-control form-control-sm" placeholder="Question"></div>
                            <div class="col-md-7"><textarea name="faqs[{{ $i }}][answer]" class="form-control form-control-sm" rows="2" placeholder="Answer">{{ $fq['answer'] ?? '' }}</textarea></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    @endforeach
                    <template>
                        <div class="row g-2 mb-2 rep-row">
                            <input type="hidden" name="faqs[__i__][id]" value="">
                            <div class="col-md-4"><input type="text" name="faqs[__i__][question]" class="form-control form-control-sm" placeholder="Question"></div>
                            <div class="col-md-7"><textarea name="faqs[__i__][answer]" class="form-control form-control-sm" rows="2" placeholder="Answer"></textarea></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-light text-danger" data-remove><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i>Add FAQ</button>
                </div>
            </div></div>
        </div>

        <div class="tab-pane fade" id="t-seo">
            <div class="card"><div class="card-body">
                <x-form.input name="seo_title" label="SEO title" :value="$service->seo_title" help="Defaults to “{name} Online in India”." />
                <x-form.textarea name="seo_description" label="Meta description" :value="$service->seo_description" rows="3" help="Aim for 140–160 characters." />
            </div></div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('admin.services.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary">Save Service</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Simple repeaters: [data-repeater] containing rows, a <template> and an [data-add] button.
    document.querySelectorAll('[data-repeater]').forEach((rep) => {
        const tpl = rep.querySelector('template');
        let next = rep.querySelectorAll('.rep-row').length + 1000;
        rep.querySelector('[data-add]').addEventListener('click', () => {
            tpl.insertAdjacentHTML('beforebegin', tpl.innerHTML.replaceAll('__i__', next++));
        });
        rep.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove]');
            if (btn) btn.closest('.rep-row').remove();
        });
    });
</script>
@endpush
