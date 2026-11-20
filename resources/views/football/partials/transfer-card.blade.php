@php $confirmed = $transfer->status->isConfirmed(); @endphp
<article class="card flex flex-col gap-3 p-4 {{ $confirmed ? '' : 'border-l-4 !border-l-amber-400' }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="font-display text-base font-bold text-navy-900 dark:text-white">
                @if ($transfer->player)
                    <a href="{{ $transfer->player->url() }}" class="hover:text-pitch-700">{{ $transfer->player_name }}</a>
                @else
                    {{ $transfer->player_name }}
                @endif
            </h3>
            <p class="mt-1 flex flex-wrap items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
                <span>{{ $transfer->fromName() ?? '—' }}</span>
                <x-svg-icon name="arrow-right" class="size-3.5 text-slate-400" />
                <span class="font-semibold">{{ $transfer->toName() ?? '—' }}</span>
            </p>
        </div>
        <span class="chip shrink-0 {{ $confirmed ? 'bg-pitch-500 text-white' : 'bg-amber-400 text-navy-900' }}">{{ $confirmed ? ($transfer->status->value === 'confirmed' ? 'Confirmed' : $transfer->status->getLabel()) : 'Rumour' }}</span>
    </div>

    <dl class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
        <div><dt class="inline font-semibold">Fee:</dt> <dd class="inline">{{ $transfer->feeLabel() }}</dd></div>
        @if ($transfer->contract_until)<div><dt class="inline font-semibold">Contract:</dt> <dd class="inline">{{ $transfer->contract_until }}</dd></div>@endif
        @if (! $confirmed && $transfer->reliability)<div><dt class="inline font-semibold">Source reliability:</dt> <dd class="inline">{{ ucfirst($transfer->reliability) }}</dd></div>@endif
    </dl>

    @if ($transfer->summary)
        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $transfer->summary }}</p>
    @endif

    <p class="mt-auto flex flex-wrap items-center gap-x-2 border-t border-slate-100 pt-2 text-xs text-slate-500 dark:border-white/5">
        <span>Source:
            @if ($transfer->source_url)
                <a href="{{ $transfer->source_url }}" target="_blank" rel="nofollow noopener" class="underline">{{ $transfer->source_name }}</a>
            @else
                {{ $transfer->source_name }}
            @endif
        </span>
        <span aria-hidden="true">·</span>
        <span>Reported <x-local-time :at="$transfer->reported_at" format="date" /></span>
        @if ($transfer->confirmed_at)
            <span aria-hidden="true">·</span><span>Confirmed <x-local-time :at="$transfer->confirmed_at" format="date" /></span>
        @endif
        @if ($transfer->article)
            <a href="{{ $transfer->article->url() }}" class="ml-auto font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Read the story</a>
        @endif
    </p>
</article>
