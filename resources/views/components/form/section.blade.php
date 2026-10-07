{{-- A titled card section of a form: <x-form.section title="Contact" text="Who to reach" icon="bi-person" tone="violet"> fields </x-form.section> --}}
@props(['title', 'text' => null, 'icon' => 'bi-pencil-square', 'tone' => ''])
<section {{ $attributes->merge(['class' => 'card form-section']) }}>
    <header class="form-section-head">
        <span class="fs-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
        <div><h2>{{ $title }}</h2>@if ($text)<p>{{ $text }}</p>@endif</div>
    </header>
    <div class="card-body row">{{ $slot }}</div>
</section>
