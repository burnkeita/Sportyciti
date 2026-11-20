@props(['title' => 'Nothing here yet', 'icon' => 'ball'])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center dark:border-white/15']) }}>
    <x-svg-icon :name="$icon" class="size-10 text-slate-300 dark:text-slate-600" />
    <p class="mt-3 font-display font-bold text-navy-900 dark:text-white">{{ $title }}</p>
    @if (trim($slot))
        <p class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">{{ $slot }}</p>
    @endif
</div>
