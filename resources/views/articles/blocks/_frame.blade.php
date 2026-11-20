{{-- Shared frame for data blocks: only verified data is rendered, always with its source. --}}
@if (! empty($data['verified']))
    <section class="card overflow-hidden" aria-label="{{ $title ?? 'Data' }}">
        @if (! empty($title))
            <header class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-white/5 dark:bg-white/5">
                <x-svg-icon :name="$icon ?? 'chart'" class="size-4 text-flame-500" />
                <h3 class="font-display text-base font-bold text-navy-900 dark:text-white">{{ $title }}</h3>
            </header>
        @endif
        <div class="p-5">{{ $slot }}</div>
        @if (! empty($data['source']))
            <footer class="border-t border-slate-100 px-5 py-2.5 text-xs text-slate-500 dark:border-white/5">
                Source:
                @if (! empty($data['source_url']))
                    <a href="{{ $data['source_url'] }}" rel="nofollow noopener" target="_blank" class="underline">{{ $data['source'] }}</a>
                @else
                    {{ $data['source'] }}
                @endif
            </footer>
        @endif
    </section>
@endif
