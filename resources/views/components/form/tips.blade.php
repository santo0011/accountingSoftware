{{-- Side panel with short guidance next to a form: <x-form.tips title="Good to know"> <li>…</li> </x-form.tips> --}}
@props(['title' => 'Good to know', 'icon' => 'bi-lightbulb'])
<aside {{ $attributes->merge(['class' => 'form-tips']) }}>
    <div class="form-tips-head"><span class="ic"><i class="bi {{ $icon }}"></i></span><strong>{{ $title }}</strong></div>
    <ul>{{ $slot }}</ul>
</aside>
