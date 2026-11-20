@php
    $f = $this->fixture;
    $live = $f->isLive();
    $started = $f->hasStarted();
@endphp
<div @if ($live || $f->kickoff_at->between(now()->subMinutes(15), now()->addMinutes(15))) wire:poll.30s.visible="refresh" @endif class="space-y-6">
    @if ($isDemo)
        <x-demo-notice />
    @endif

    {{-- Score header --}}
    <section class="relative overflow-hidden rounded-2xl bg-navy-900 px-4 py-8 text-white sm:px-8">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(18,183,106,0.25),transparent_60%)]" aria-hidden="true"></div>
        <div class="relative">
            <div class="mb-6 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-center text-xs font-semibold uppercase tracking-wider text-slate-300">
                <a href="{{ $f->competition->url() }}" class="hover:text-white">{{ $f->competition->name }}</a>
                @if ($f->round)<span aria-hidden="true">·</span><span>{{ $f->round }}</span>@endif
            </div>

            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-8">
                @foreach (['home' => $f->homeTeam, 'away' => $f->awayTeam] as $side => $team)
                    @if ($side === 'away')
                        {{-- score column --}}
                        <div class="text-center">
                            @if ($started)
                                <p class="font-display text-4xl font-extrabold tabular-nums sm:text-6xl" aria-live="polite">
                                    {{ $f->home_score }}<span class="mx-2 text-slate-500">–</span>{{ $f->away_score }}
                                </p>
                                @if ($f->home_pen_score !== null)
                                    <p class="mt-1 text-xs text-slate-300">Pens {{ $f->home_pen_score }}–{{ $f->away_pen_score }}</p>
                                @endif
                                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $live ? 'bg-flame-500 text-white' : 'bg-white/10 text-slate-200' }}">
                                    @if ($live)<span class="live-dot size-1.5 rounded-full bg-white"></span>@endif
                                    {{ $live && $f->status !== 'HT' && $f->elapsed ? $f->elapsed."' · " : '' }}{{ $f->statusLabel() }}
                                </p>
                            @else
                                <p class="font-display text-3xl font-extrabold sm:text-5xl"><x-local-time :at="$f->kickoff_at" format="time" /></p>
                                <p class="mt-2 text-xs font-semibold text-slate-300"><x-local-time :at="$f->kickoff_at" format="date" /></p>
                                @if (in_array($f->status, ['PST', 'CANC', 'ABD'], true))
                                    <p class="mt-2 rounded-full bg-red-500/20 px-3 py-1 text-xs font-bold text-red-200">{{ $f->statusLabel() }}</p>
                                @endif
                            @endif
                        </div>
                    @endif
                    <a href="{{ $team->url() }}" class="group flex flex-col items-center gap-3 text-center {{ $side === 'home' ? 'order-first' : '' }}">
                        <x-team-logo :team="$team" size="size-14 sm:size-20" />
                        <span class="font-display text-base font-bold leading-tight group-hover:underline sm:text-xl">{{ $team->name }}</span>
                    </a>
                @endforeach
            </div>

            @if ($f->home_ht_score !== null && $started)
                <p class="mt-4 text-center text-xs text-slate-400">Half-time: {{ $f->home_ht_score }}–{{ $f->away_ht_score }}</p>
            @endif

            {{-- Goal scorers summary --}}
            @php $goals = $f->events->where('type', 'goal')->reject(fn ($e) => str_contains((string) $e->detail, 'Missed')); @endphp
            @if ($goals->isNotEmpty())
                <div class="mx-auto mt-6 grid max-w-xl grid-cols-2 gap-6 text-xs text-slate-300">
                    @foreach ([$f->home_team_id, $f->away_team_id] as $i => $teamId)
                        <ul class="space-y-1 {{ $i === 0 ? 'text-right' : '' }}">
                            @foreach ($goals->where('team_id', $teamId) as $goal)
                                <li>{{ $goal->player_name }} {{ $goal->minuteLabel() }}@if ($goal->detail === 'Own Goal') (OG)@elseif ($goal->detail === 'Penalty') (pen)@endif</li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Section tabs --}}
    <nav class="flex gap-1 overflow-x-auto scrollbar-none" aria-label="Match centre sections">
        @foreach (['summary' => 'Timeline', 'stats' => 'Statistics', 'lineups' => 'Line-ups', 'h2h' => 'Form & H2H'] as $key => $label)
            <button type="button" wire:click="show('{{ $key }}')" class="tab {{ $section === $key ? 'tab-active' : '' }}" aria-pressed="{{ $section === $key ? 'true' : 'false' }}">{{ $label }}</button>
        @endforeach
    </nav>

    <div wire:loading.delay.class="opacity-60" class="transition-opacity">
        @if ($section === 'summary')
            <div class="grid gap-6 lg:grid-cols-3">
                <section class="card p-5 lg:col-span-2" aria-label="Match timeline">
                    <h2 class="section-title mb-5">Match timeline</h2>
                    @if ($f->events->isEmpty())
                        <x-empty-state :title="$started ? 'No events recorded yet' : 'The match has not started'" icon="clock">
                            Goals, cards and substitutions appear here as they happen.
                        </x-empty-state>
                    @else
                        <ol class="relative space-y-3 before:absolute before:inset-y-0 before:left-1/2 before:w-px before:bg-slate-200 dark:before:bg-white/10">
                            @foreach ($f->events->sortByDesc(fn ($e) => $e->minute * 100 + ($e->extra_minute ?? 0)) as $event)
                                @php
                                    $isHome = $event->team_id === $f->home_team_id;
                                    [$icon, $color] = match (true) {
                                        $event->type === 'goal' && ! str_contains((string) $event->detail, 'Missed') => ['goal', 'text-pitch-600'],
                                        $event->type === 'card' && str_contains((string) $event->detail, 'Red') => ['card', 'text-red-600'],
                                        $event->type === 'card' => ['card', 'text-yellow-400'],
                                        $event->type === 'subst' => ['sub', 'text-sky-600'],
                                        $event->type === 'var' => ['chart', 'text-violet-600'],
                                        default => ['whistle', 'text-slate-500'],
                                    };
                                @endphp
                                <li class="relative grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <div class="{{ $isHome ? 'text-right' : 'invisible' }}">
                                        @if ($isHome) @include('livewire.football.partials.event', ['event' => $event]) @endif
                                    </div>
                                    <div class="relative z-10 flex flex-col items-center rounded-full bg-white px-1 dark:bg-navy-900">
                                        <x-svg-icon :name="$icon" class="size-5 {{ $color }}" />
                                        <span class="text-[11px] font-bold tabular-nums text-slate-500">{{ $event->minuteLabel() }}</span>
                                    </div>
                                    <div class="{{ ! $isHome ? '' : 'invisible' }}">
                                        @if (! $isHome) @include('livewire.football.partials.event', ['event' => $event]) @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <aside class="card space-y-3 p-5 text-sm" aria-label="Match information">
                    <h2 class="section-title mb-2">Match info</h2>
                    <dl class="space-y-3">
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Kick-off</dt><dd class="font-medium"><x-local-time :at="$f->kickoff_at" format="datetime" /></dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-400">Competition</dt><dd class="font-medium"><a class="hover:underline" href="{{ $f->competition->url() }}">{{ $f->competition->name }}</a></dd></div>
                        @if ($f->venue_name)<div><dt class="text-xs font-semibold uppercase text-slate-400">Venue</dt><dd class="font-medium">{{ $f->venue_name }}{{ $f->venue_city ? ', '.$f->venue_city : '' }}</dd></div>@endif
                        @if ($f->referee)<div><dt class="text-xs font-semibold uppercase text-slate-400">Referee</dt><dd class="font-medium">{{ $f->referee }}</dd></div>@endif
                    </dl>
                    <p class="border-t border-slate-100 pt-3 text-xs text-slate-400 dark:border-white/5">
                        Data: {{ $isDemo ? 'fictional demonstration data' : 'licensed sports data provider' }}
                        @if ($f->details_synced_at) · updated <x-local-time :at="$f->details_synced_at" format="relative" /> @endif
                    </p>
                </aside>
            </div>

        @elseif ($section === 'stats')
            <section class="card mx-auto max-w-2xl p-5" aria-label="Match statistics">
                <div class="mb-5 flex items-center justify-between text-sm font-bold">
                    <span class="flex items-center gap-2"><x-team-logo :team="$f->homeTeam" /> {{ $f->homeTeam->displayName() }}</span>
                    <span class="flex items-center gap-2">{{ $f->awayTeam->displayName() }} <x-team-logo :team="$f->awayTeam" /></span>
                </div>
                @forelse ($this->stats as $type => $values)
                    @php
                        $h = (float) str_replace('%', '', (string) $values['home']);
                        $a = (float) str_replace('%', '', (string) $values['away']);
                        $total = max($h + $a, 0.0001);
                    @endphp
                    <div class="mb-4">
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-bold tabular-nums">{{ $values['home'] ?? 0 }}</span>
                            <span class="text-xs font-medium text-slate-500">{{ $type }}</span>
                            <span class="font-bold tabular-nums">{{ $values['away'] ?? 0 }}</span>
                        </div>
                        <div class="flex h-2 gap-1" aria-hidden="true">
                            <div class="flex flex-1 justify-end overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="rounded-full bg-pitch-500" style="width: {{ round($h / $total * 100) }}%"></div></div>
                            <div class="flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-navy-500 dark:bg-flame-500" style="width: {{ round($a / $total * 100) }}%"></div></div>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="Statistics not available yet" icon="chart">Statistics are published by our data provider during and after the match.</x-empty-state>
                @endforelse
            </section>

        @elseif ($section === 'lineups')
            @if ($f->lineups->isEmpty())
                <x-empty-state title="Line-ups not announced yet" icon="user">Official line-ups are usually confirmed about an hour before kick-off.</x-empty-state>
            @else
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ([$f->homeTeam, $f->awayTeam] as $team)
                        @php $lineup = $f->lineups->firstWhere('team_id', $team->id); @endphp
                        <section class="card p-5" aria-label="{{ $team->name }} line-up">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <h2 class="flex items-center gap-2 font-display font-bold"><x-team-logo :team="$team" /> {{ $team->name }}</h2>
                                @if ($lineup?->formation)<span class="chip bg-navy-900 text-white dark:bg-white/10">{{ $lineup->formation }}</span>@endif
                            </div>
                            @if ($lineup)
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Starting XI</h3>
                                <ul class="mb-5 space-y-1.5 text-sm">
                                    @foreach ($lineup->starting_xi ?? [] as $p)
                                        <li class="flex items-center gap-3"><span class="w-6 text-right font-bold tabular-nums text-slate-400">{{ $p['number'] ?? '' }}</span> {{ $p['name'] }} @if (! empty($p['position']))<span class="text-xs text-slate-400">{{ $p['position'] }}</span>@endif</li>
                                    @endforeach
                                </ul>
                                @if (! empty($lineup->substitutes))
                                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Substitutes</h3>
                                    <ul class="space-y-1.5 text-sm text-slate-600 dark:text-slate-300">
                                        @foreach ($lineup->substitutes as $p)
                                            <li class="flex items-center gap-3"><span class="w-6 text-right font-bold tabular-nums text-slate-400">{{ $p['number'] ?? '' }}</span> {{ $p['name'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if ($lineup->coach_name)
                                    <p class="mt-4 border-t border-slate-100 pt-3 text-sm dark:border-white/5"><span class="text-slate-400">Coach:</span> {{ $lineup->coach_name }}</p>
                                @endif
                            @else
                                <p class="text-sm text-slate-500">Not available.</p>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif

        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="card p-5" aria-label="Recent form">
                    <h2 class="section-title mb-4">Recent form</h2>
                    @foreach (['home' => $f->homeTeam, 'away' => $f->awayTeam] as $side => $team)
                        <div class="mb-5">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2 text-sm font-bold"><x-team-logo :team="$team" /> {{ $team->name }}</span>
                                <span class="flex gap-1">
                                    @foreach ($this->form[$side]->reverse() as $past)
                                        <x-form-badge :result="$past->resultFor($team->id)" />
                                    @endforeach
                                </span>
                            </div>
                            <div class="divide-y divide-slate-100 rounded-lg ring-1 ring-slate-100 dark:divide-white/5 dark:ring-white/5">
                                @forelse ($this->form[$side] as $past)
                                    <x-fixture-row :fixture="$past" show-date class="!py-2" />
                                @empty
                                    <p class="px-4 py-3 text-sm text-slate-500">No recent results stored.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </section>

                <section class="card p-5" aria-label="Head to head">
                    <h2 class="section-title mb-4">Head-to-head</h2>
                    @php $h2h = $this->headToHead; @endphp
                    @if (collect($h2h['meetings'])->isEmpty())
                        <x-empty-state title="No previous meetings found" icon="swap" />
                    @else
                        @php
                            $wins = ['home' => 0, 'draw' => 0, 'away' => 0];
                            foreach ($h2h['meetings'] as $m) {
                                [$hId, $hs, $as] = $h2h['source'] === 'local'
                                    ? [$m->home_team_id === $f->home_team_id, $m->home_score, $m->away_score]
                                    : [$m['home']['external_id'] === $f->homeTeam->external_id, $m['score']['home'], $m['score']['away']];
                                $forHome = $hId ? $hs : $as; $forAway = $hId ? $as : $hs;
                                $wins[$forHome > $forAway ? 'home' : ($forHome < $forAway ? 'away' : 'draw')]++;
                            }
                        @endphp
                        <div class="mb-5 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-pitch-50 p-3 dark:bg-pitch-500/10"><p class="text-2xl font-extrabold text-pitch-700 dark:text-pitch-300">{{ $wins['home'] }}</p><p class="text-xs text-slate-500">{{ $f->homeTeam->displayName() }} wins</p></div>
                            <div class="rounded-lg bg-slate-100 p-3 dark:bg-white/5"><p class="text-2xl font-extrabold">{{ $wins['draw'] }}</p><p class="text-xs text-slate-500">Draws</p></div>
                            <div class="rounded-lg bg-navy-50 p-3 dark:bg-white/5"><p class="text-2xl font-extrabold text-navy-700 dark:text-white">{{ $wins['away'] }}</p><p class="text-xs text-slate-500">{{ $f->awayTeam->displayName() }} wins</p></div>
                        </div>
                        <ul class="divide-y divide-slate-100 text-sm dark:divide-white/5">
                            @foreach ($h2h['meetings'] as $m)
                                <li class="flex items-center justify-between gap-3 py-2">
                                    @if ($h2h['source'] === 'local')
                                        <x-local-time :at="$m->kickoff_at" format="date" class="w-24 shrink-0 text-xs text-slate-500" />
                                        <span class="flex-1 truncate text-right">{{ $m->homeTeam->name }}</span>
                                        <span class="rounded bg-slate-100 px-2 font-bold tabular-nums dark:bg-white/10">{{ $m->home_score }}–{{ $m->away_score }}</span>
                                        <span class="flex-1 truncate">{{ $m->awayTeam->name }}</span>
                                    @else
                                        <time class="w-24 shrink-0 text-xs text-slate-500" datetime="{{ $m['kickoff_at'] }}" data-local="date">{{ \Illuminate\Support\Carbon::parse($m['kickoff_at'])->format('j M Y') }}</time>
                                        <span class="flex-1 truncate text-right">{{ $m['home']['name'] }}</span>
                                        <span class="rounded bg-slate-100 px-2 font-bold tabular-nums dark:bg-white/10">{{ $m['score']['home'] }}–{{ $m['score']['away'] }}</span>
                                        <span class="flex-1 truncate">{{ $m['away']['name'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-slate-400">Source: {{ $h2h['source'] === 'provider' ? ($isDemo ? 'demonstration data' : 'licensed sports data provider') : 'SportyCiti match database' }}.</p>
                    @endif
                </section>
            </div>
        @endif
    </div>
</div>
