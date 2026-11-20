@props(['result'])
@php
    $classes = match ($result) {
        'W' => 'bg-pitch-500 text-white',
        'L' => 'bg-red-500 text-white',
        'D' => 'bg-slate-400 text-white',
        default => 'bg-slate-200 text-slate-500',
    };
    $label = ['W' => 'Win', 'D' => 'Draw', 'L' => 'Loss'][$result] ?? 'Unknown';
@endphp
<span {{ $attributes->merge(['class' => "grid size-5 place-items-center rounded text-[10px] font-bold {$classes}"]) }} title="{{ $label }}">
    {{ $result }}<span class="sr-only"> ({{ $label }})</span>
</span>
