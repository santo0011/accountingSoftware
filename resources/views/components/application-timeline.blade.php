@props(['application'])
@php
    $statusEnum = \App\Enums\ApplicationStatus::class;
    $stage = $application->status->stage();
    $closed = in_array($application->status, [$statusEnum::Rejected, $statusEnum::Cancelled], true);
    $completed = $application->status === $statusEnum::Completed;
    // Date each stage was first reached, from the status history.
    $reached = [];
    foreach ($application->histories as $history) {
        $reached[$history->to_status->stage()] ??= $history->created_at;
    }
@endphp
<ul class="progress-timeline">
    @foreach ($statusEnum::TIMELINE as $n => $title)
        @php($state = $closed ? ($n === 1 ? 'done' : '') : (($n < $stage || $completed) ? 'done' : ($n === $stage ? 'current' : '')))
        <li class="{{ $state }}">
            <span class="dot">@if ($state === 'done')<i class="bi bi-check-lg"></i>@else{{ $n }}@endif</span>
            <div class="t-title">{{ $title }}</div>
            <div class="t-meta">
                @if (isset($reached[$n]) && in_array($state, ['done', 'current'], true))
                    {{ $reached[$n]->format('d M Y, h:i A') }}
                @elseif ($state === 'current')
                    In progress
                @endif
            </div>
        </li>
    @endforeach
    @if ($closed)
        <li class="current">
            <span class="dot text-danger" style="border-color:var(--brand-red)"><i class="bi bi-x-lg"></i></span>
            <div class="t-title text-danger">{{ $application->status->label() }}</div>
            <div class="t-meta">{{ $application->histories->last()?->remarks }}</div>
        </li>
    @endif
</ul>
