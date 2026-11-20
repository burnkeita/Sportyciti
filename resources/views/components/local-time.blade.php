@props(['at', 'format' => 'datetime'])
{{-- Server renders an Africa/Accra fallback; JavaScript replaces it with the visitor's local time. --}}
@if ($at)
    @php
        $fallback = $at->copy()->setTimezone(\App\Support\VisitorTime::timezone());
        $text = match ($format) {
            'time' => $fallback->format('H:i'),
            'date' => $fallback->format('j M Y'),
            'day' => $fallback->format('D j M'),
            'relative' => $at->diffForHumans(),
            default => $fallback->format('D j M, H:i'),
        };
    @endphp
    <time datetime="{{ $at->copy()->utc()->toIso8601String() }}" @if ($format !== 'relative') data-local="{{ $format }}" @endif {{ $attributes }}>{{ $text }}</time>
@endif
