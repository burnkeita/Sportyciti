@props(['title', 'href' => null, 'linkText' => 'See all', 'as' => 'h2'])
<div {{ $attributes->merge(['class' => 'mb-5 flex items-end justify-between gap-4 border-b border-slate-200 pb-3 dark:border-white/10']) }}>
    <{{ $as }} class="section-title">{{ $title }}</{{ $as }}>
    <div class="flex items-center gap-3">
        {{ $slot }}
        @if ($href)
            <a href="{{ $href }}" class="group inline-flex items-center gap-1 text-sm font-semibold text-pitch-700 hover:text-pitch-600 dark:text-pitch-400">
                {{ $linkText }} <x-svg-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />
            </a>
        @endif
    </div>
</div>
