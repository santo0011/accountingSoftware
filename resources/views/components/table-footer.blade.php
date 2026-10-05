@props(['items', 'label' => 'records'])
{{-- Footer for paginated tables: "Showing 1–20 of 54", rows-per-page picker and page links. --}}
@php
    $perPage = $items->perPage();
    $options = collect([10, 20, 50, 100])->push($perPage)->unique()->sort()->values();
@endphp
<div class="table-footer tf">
    <div class="tf-info">
        @if ($items->total())
            Showing <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> of <strong>{{ number_format($items->total()) }}</strong> {{ $label }}
        @else
            No {{ $label }}
        @endif
    </div>

    <form method="GET" class="tf-size">
        {{-- keep the current filters; go back to page 1 when the page size changes --}}
        @foreach (request()->except(['page', 'per_page']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $v)<input type="hidden" name="{{ $key }}[]" value="{{ $v }}">@endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label for="tf-per-page-{{ $label }}">Rows</label>
        <select id="tf-per-page-{{ $label }}" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach ($options as $n)<option value="{{ $n }}" @selected($n === $perPage)>{{ $n }}</option>@endforeach
        </select>
    </form>

    {{ $items->onEachSide(1)->links() }}
</div>
