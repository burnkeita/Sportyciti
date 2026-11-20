@props(['fixture', 'showCompetition' => false, 'showDate' => false])
@php
    $live = $fixture->isLive();
    $started = $fixture->hasStarted();
@endphp
<a href="{{ $fixture->url() }}" {{ $attributes->merge(['class' => 'group grid grid-cols-[4.25rem_1fr_auto] items-center gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-white/5']) }}>
    <div class="text-center text-xs font-semibold">
        @if ($live)
            <span class="inline-flex items-center gap-1 text-flame-600 dark:text-flame-400">
                <span class="live-dot size-1.5 rounded-full bg-flame-500"></span>
                {{ $fixture->status === 'HT' ? 'HT' : ($fixture->elapsed ? $fixture->elapsed."'" : 'Live') }}
            </span>
        @elseif ($fixture->isFinished())
            <span class="text-slate-500 dark:text-slate-400">{{ $fixture->status }}</span>
        @elseif (in_array($fixture->status, ['PST', 'CANC', 'ABD'], true))
            <span class="text-[10px] uppercase text-slate-500">{{ $fixture->status === 'PST' ? 'Postp.' : ($fixture->status === 'CANC' ? 'Canc.' : 'Aband.') }}</span>
        @else
            @if ($showDate)
                <x-local-time :at="$fixture->kickoff_at" format="day" class="block text-[10px] font-medium text-slate-500" />
            @endif
            <x-local-time :at="$fixture->kickoff_at" format="time" class="text-navy-900 dark:text-white" />
        @endif
    </div>

    <div class="min-w-0 space-y-1.5">
        @if ($showCompetition)
            <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $fixture->competition->displayName() }}</p>
        @endif
        @foreach (['home' => $fixture->homeTeam, 'away' => $fixture->awayTeam] as $side => $team)
            @php $score = $side === 'home' ? $fixture->home_score : $fixture->away_score; $other = $side === 'home' ? $fixture->away_score : $fixture->home_score; @endphp
            <div class="flex items-center justify-between gap-3">
                <span class="flex min-w-0 items-center gap-2">
                    <x-team-logo :team="$team" size="size-5" />
                    <span class="truncate text-sm {{ $fixture->isFinished() && $score > $other ? 'font-bold text-navy-900 dark:text-white' : 'font-medium text-slate-700 dark:text-slate-300' }}">{{ $team->name }}</span>
                </span>
                <span class="w-5 text-right text-sm font-bold tabular-nums {{ $live ? 'text-flame-600 dark:text-flame-400' : 'text-navy-900 dark:text-white' }}">{{ $started ? $score : '' }}</span>
            </div>
        @endforeach
    </div>

    <x-svg-icon name="chevron-right" class="size-4 text-slate-300 transition group-hover:text-slate-500" />
</a>
