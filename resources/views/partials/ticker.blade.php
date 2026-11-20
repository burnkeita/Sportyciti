<div class="bg-flame-500 text-white" role="region" aria-label="Breaking news">
    <div class="container-page flex h-10 items-center gap-3 overflow-hidden">
        <span class="flex shrink-0 items-center gap-1.5 rounded bg-navy-900 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wider">
            <span class="live-dot size-1.5 rounded-full bg-flame-400"></span> Breaking
        </span>
        <div class="relative flex-1 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_3%,black_97%,transparent)]">
            <div class="ticker-track flex w-max gap-10 whitespace-nowrap text-sm font-semibold" style="--ticker-duration: {{ max(25, $breaking->count() * 12) }}s">
                @foreach ([1, 2] as $copy)
                    @foreach ($breaking as $item)
                        <a href="{{ $item->url() }}" class="hover:underline" @if ($copy === 2) aria-hidden="true" tabindex="-1" @endif>{{ $item->title }}</a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>
