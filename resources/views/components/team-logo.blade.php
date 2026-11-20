@props(['team', 'size' => 'size-6'])
@if ($team?->logo_url)
    <img src="{{ $team->logo_url }}" alt="" loading="lazy" decoding="async" width="48" height="48" {{ $attributes->merge(['class' => "{$size} shrink-0 object-contain"]) }}>
@else
    <span {{ $attributes->merge(['class' => "{$size} grid shrink-0 place-items-center rounded-full bg-slate-200 text-[9px] font-bold uppercase text-slate-600 dark:bg-white/10 dark:text-slate-300"]) }} aria-hidden="true">
        {{ \Illuminate\Support\Str::of($team?->name ?? '?')->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
    </span>
@endif
