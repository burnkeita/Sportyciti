<div class="space-y-5">
    @if ($isDemo)
        <x-demo-notice compact />
    @endif

    <div class="card flex flex-col gap-3 p-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="group" aria-label="Date range">
                @foreach (['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $key => $label)
                    <button type="button" wire:click="$set('range', '{{ $key }}')" aria-pressed="{{ $range === $key ? 'true' : 'false' }}"
                            class="rounded-md px-3 py-1.5 text-sm font-semibold transition {{ $range === $key ? 'bg-white text-navy-900 shadow-sm dark:bg-navy-800 dark:text-white' : 'text-slate-600 dark:text-slate-300' }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex items-center gap-1">
                <button type="button" wire:click="move(-1)" class="grid size-9 place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Previous period"><x-svg-icon name="chevron-left" class="size-4" /></button>
                <span class="min-w-32 text-center text-sm font-bold text-navy-900 dark:text-white">
                    @if ($range === 'day')
                        {{ $start->format('D j M Y') }}
                    @elseif ($range === 'month')
                        {{ $start->format('F Y') }}
                    @else
                        {{ $start->format('j M') }} – {{ $end->format('j M Y') }}
                    @endif
                </span>
                <button type="button" wire:click="move(1)" class="grid size-9 place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Next period"><x-svg-icon name="chevron-right" class="size-4" /></button>
                @if ($offset !== 0)
                    <button type="button" wire:click="$set('offset', 0)" class="ml-1 text-xs font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Today</button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex">
            @if ($competitions->isNotEmpty())
                <label class="sr-only" for="fb-comp">League</label>
                <select id="fb-comp" wire:model.live="competition" class="input py-2 pr-8 sm:w-auto">
                    <option value="">All leagues</option>
                    @foreach ($competitions as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            @endif
            <label class="sr-only" for="fb-team">Team</label>
            <select id="fb-team" wire:model.live="team" class="input py-2 pr-8 sm:w-auto sm:max-w-56">
                <option value="">All teams</option>
                @foreach ($teams as $t)
                    <option value="{{ $t->slug }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div wire:loading.delay.class="opacity-60" class="space-y-6 transition-opacity">
        @forelse ($this->days as $date => $fixtures)
            <section wire:key="day-{{ $date }}">
                <h2 class="mb-2 font-display text-sm font-extrabold uppercase tracking-wide text-slate-500">
                    {{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : \Carbon\Carbon::parse($date)->format('l j F') }}
                </h2>
                <div class="card divide-y divide-slate-100 overflow-hidden dark:divide-white/5">
                    @foreach ($fixtures as $fixture)
                        <x-fixture-row :fixture="$fixture" :show-competition="! $lockedCompetition" wire:key="fb-{{ $fixture->id }}" />
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state :title="$mode === 'results' ? 'No results in this period' : 'No fixtures in this period'" icon="calendar">
                Try a different date range, league or team.
            </x-empty-state>
        @endforelse
    </div>

    <p class="text-center text-xs text-slate-500">All kick-off times are shown in your local timezone.</p>
</div>
