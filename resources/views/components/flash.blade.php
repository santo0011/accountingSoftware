@foreach (['success' => 'bi-check-circle', 'error' => 'bi-exclamation-octagon', 'warning' => 'bi-exclamation-triangle', 'info' => 'bi-info-circle'] as $type => $icon)
    @if (session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show d-flex gap-2 align-items-start" role="alert">
            <i class="bi {{ $icon }} mt-1"></i><div>{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach
@if (session('status'))
    <div class="alert alert-success d-flex gap-2"><i class="bi bi-check-circle mt-1"></i><div>{{ session('status') }}</div></div>
@endif
@if ($errors->any() && ($summary ?? true))
    <div class="alert alert-danger d-flex gap-2" role="alert">
        <i class="bi bi-exclamation-octagon mt-1"></i>
        <div>Please fix the highlighted fields.
            @if ($errors->count() <= 3)
                <ul class="mb-0 mt-1 ps-3 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            @endif
        </div>
    </div>
@endif
