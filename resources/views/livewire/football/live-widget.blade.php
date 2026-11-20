<div @if ($liveCount > 0) wire:poll.60s.visible @endif>
    @if ($isDemo)
        <x-demo-notice compact class="mb-3" />
    @endif

    @if ($fixtures->isEmpty())
        <x-empty-state title="No featured matches right now" icon="calendar">Check the full fixture list for upcoming games.</x-empty-state>
    @elseif ($variant === 'strip')
        <div class="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-2 scrollbar-none sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-4">
            @foreach ($fixtures as $fixture)
                <a href="{{ $fixture->url() }}" wire:key="lw-{{ $fixture->id }}" class="card w-64 shrink-0 snap-start p-4 transition hover:-translate-y-0.5 hover:shadow-md sm:w-auto">
                    <div class="mb-3 flex items-center justify-between gap-2 text-[11px] font-semibold uppercase tracking-wide">
                        <span class="truncate text-slate-400">{{ $fixture->competition->displayName() }}</span>
                        @if ($fixture->isLive())
                            <span class="inline-flex shrink-0 items-center gap-1 rounded bg-flame-500 px-1.5 py-0.5 text-white">
                                <span class="live-dot size-1.5 rounded-full bg-white"></span>{{ $fixture->status === 'HT' ? 'HT' : $fixture->elapsed."'" }}
                            </span>
                        @elseif ($fixture->isFinished())
                            <span class="shrink-0 text-slate-500">FT</span>
                        @else
                            <x-local-time :at="$fixture->kickoff_at" format="datetime" class="shrink-0 text-pitch-600 dark:text-pitch-400" />
                        @endif
                    </div>
                    @foreach ([[$fixture->homeTeam, $fixture->home_score], [$fixture->awayTeam, $fixture->away_score]] as [$team, $score])
                        <div class="flex items-center justify-between gap-2 py-1">
                            <span class="flex min-w-0 items-center gap-2">
                                <x-team-logo :team="$team" />
                                <span class="truncate text-sm font-semibold text-navy-900 dark:text-white">{{ $team->name }}</span>
                            </span>
                            <span class="text-base font-extrabold tabular-nums text-navy-900 dark:text-white">{{ $fixture->hasStarted() ? $score : '–' }}</span>
                        </div>
                    @endforeach
                </a>
            @endforeach
        </div>
    @else
        <div class="divide-y divide-slate-100 dark:divide-white/5">
            @foreach ($fixtures as $fixture)
                <x-fixture-row :fixture="$fixture" show-competition show-date wire:key="lw-{{ $fixture->id }}" />
            @endforeach
        </div>
    @endif
</div>
