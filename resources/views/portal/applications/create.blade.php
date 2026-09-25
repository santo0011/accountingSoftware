@extends('layouts.portal')
@section('title', (string) ('Apply: '.$service->name))

@section('content')
<x-page-header :title="'Apply for '.$service->name" :subtitle="$service->short_description" :back="route('site.services.show', $service->slug)" />

<form method="POST" action="{{ route('portal.applications.store', $service->slug) }}" enctype="multipart/form-data" novalidate>
    @csrf
    <div class="row g-4" data-wizard>
        <div class="col-lg-8">
            <div class="wizard-steps">
                <div class="ws"><span class="n">1</span><span class="lbl">Details</span></div>
                <div class="ws"><span class="n">2</span><span class="lbl">Documents</span></div>
                <div class="ws"><span class="n">3</span><span class="lbl">Review</span></div>
            </div>

            {{-- Step 1: business + service questions --}}
            <div class="wizard-pane card">
                <div class="card-header">Business &amp; service details</div>
                <div class="card-body">
                    @if ($businesses->isNotEmpty())
                        <x-form.select name="business_id" label="Apply for business" :options="$businesses->pluck('name', 'id')->all()"
                            :value="$businesses->first()->id" placeholder="+ Add a new business" data-review-label="Business"
                            onchange="document.getElementById('newBusiness').classList.toggle('d-none', this.value !== '')" />
                    @endif
                    <div id="newBusiness" class="{{ $businesses->isNotEmpty() && ! old('new_business_name') ? 'd-none' : '' }} border rounded p-3 mb-3 bg-soft">
                        <div class="small fw-semibold text-navy mb-2">New business</div>
                        <div class="row">
                            <x-form.input name="new_business_name" label="Business / proposed name" col="col-md-6 mb-3" data-review-label="New business" />
                            <x-form.select name="new_business_type" label="Business type" :options="\App\Models\Business::TYPES" placeholder="Select" col="col-md-6 mb-3" />
                            <x-form.input name="new_business_state" label="State" col="col-md-6 mb-0" placeholder="e.g. Maharashtra" />
                        </div>
                    </div>

                    @foreach ($service->fields as $field)
                        @php($fname = 'fields['.$field->name.']')
                        @switch($field->type)
                            @case('select')
                                <x-form.select :name="$fname" :label="$field->label" :options="array_combine($field->options ?? [], $field->options ?? [])" :required="$field->is_required" placeholder="Select" :data-review-label="$field->label" />
                                @break
                            @case('textarea')
                                <x-form.textarea :name="$fname" :label="$field->label" :required="$field->is_required" :placeholder="$field->placeholder" :data-review-label="$field->label" />
                                @break
                            @default
                                <x-form.input :name="$fname" :type="$field->type" :label="$field->label" :required="$field->is_required" :placeholder="$field->placeholder" :data-review-label="$field->label" />
                        @endswitch
                    @endforeach
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-next>Continue <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>

            {{-- Step 2: documents --}}
            <div class="wizard-pane card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Upload documents
                    <span class="small text-muted fw-normal">PDF, JPG, PNG, DOC · max 5 MB each</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Don't have everything handy? You can submit now and upload the remaining documents later from your application page.</div>
                    @foreach ($service->documents as $doc)
                        @php($key = 'documents.'.$doc->id)
                        <div class="upload-box mb-3 {{ $errors->has($key) ? 'border-danger' : '' }}">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <i class="bi bi-file-earmark-arrow-up fs-3 text-brand"></i>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-navy small">{{ $doc->name }} @if ($doc->is_mandatory)<span class="badge badge-soft-warning ms-1">Required</span>@endif</div>
                                    <div class="small text-muted" data-file-name data-empty="No file chosen">No file chosen</div>
                                </div>
                                <label class="btn btn-sm btn-outline-primary mb-0">
                                    Choose file
                                    <input type="file" name="documents[{{ $doc->id }}]" class="d-none {{ $errors->has($key) ? 'is-invalid' : '' }}" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" data-review-label="{{ $doc->name }}">
                                </label>
                            </div>
                            @error($key)<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-prev><i class="bi bi-arrow-left me-1"></i> Back</button>
                    <button type="button" class="btn btn-primary" data-next>Review <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>

            {{-- Step 3: review --}}
            <div class="wizard-pane card">
                <div class="card-header">Review your application</div>
                <div class="card-body">
                    <dl class="dl-grid mb-4" data-review></dl>
                    <div class="form-check">
                        <input class="form-check-input @error('confirm') is-invalid @enderror" type="checkbox" name="confirm" id="confirm" value="1" required>
                        <label class="form-check-label small" for="confirm">I confirm the information and documents provided are correct, and I agree to the <a href="{{ route('site.terms') }}" target="_blank">terms</a> and <a href="{{ route('site.refund') }}" target="_blank">refund policy</a>.</label>
                        @error('confirm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-prev><i class="bi bi-arrow-left me-1"></i> Back</button>
                    <button type="submit" class="btn btn-cta">{{ $quote['total'] > 0 ? 'Submit & Proceed to Payment' : 'Submit Application' }} <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>
        </div>

        {{-- Order summary --}}
        <div class="col-lg-4">
            <div class="card position-sticky" style="top: 88px">
                <div class="card-header">Order summary</div>
                <div class="card-body">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <span class="icon-bubble"><i class="bi {{ $service->iconClass() }}"></i></span>
                        <div><div class="fw-semibold text-navy">{{ $service->name }}</div><div class="small text-muted">{{ $service->processing_time }}</div></div>
                    </div>
                    <dl class="dl-grid">
                        <dt>Professional fee</dt><dd class="text-end">{{ money($quote['amount']) }}</dd>
                        @if ($quote['discount'] > 0)<dt>Discount</dt><dd class="text-end text-green">− {{ money($quote['discount']) }}</dd>@endif
                        @if ($quote['intra_state'])
                            <dt>CGST ({{ $quote['tax_rate'] / 2 }}%)</dt><dd class="text-end">{{ money($quote['cgst']) }}</dd>
                            <dt>SGST ({{ $quote['tax_rate'] / 2 }}%)</dt><dd class="text-end">{{ money($quote['sgst']) }}</dd>
                        @else
                            <dt>IGST ({{ $quote['tax_rate'] }}%)</dt><dd class="text-end">{{ money($quote['igst']) }}</dd>
                        @endif
                    </dl>
                    <div class="divider"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-navy">Total payable</span>
                        <span class="fs-4 fw-bold text-navy">{{ money($quote['total']) }}</span>
                    </div>
                    <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Government fees (if any) are charged at actuals. Final tax is based on your business state.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
