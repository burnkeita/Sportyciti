<div @if ($this->shouldPoll()) wire:poll.30s.visible @endif class="space-y-5">
    @if ($isDemo)
        <x-demo-notice />
    @endif

    {{-- Tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="-mx-1 flex gap-1 overflow-x-auto px-1 scrollbar-none" role="tablist" aria-label="Match status">
            @foreach (['all' => 'All matches', 'live' => 'Live', 'upcoming' => 'Upcoming', 'results' => 'Completed'] as $key => $label)
                <button type="button" role="tab" wire:click="setTab('{{ $key }}')" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                        class="tab {{ $tab === $key ? 'tab-active' : '' }}">
                    @if ($key === 'live')
                        <span class="inline-flex items-center gap-1.5">
                            <span class="size-1.5 rounded-full bg-flame-500 {{ $liveCount ? 'live-dot' : 'opacity-40' }}"></span>
                            Live @if ($liveCount)<span class="rounded bg-flame-500 px-1.5 text-[11px] text-white">{{ $liveCount }}</span>@endif
                        </span>
                    @else
                        {{ $label }}
                    @endif
                </button>
            @endforeach
        </div>

        <div class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto">
            <label class="sr-only" for="sb-country">Country</label>
            <select id="sb-country" wire:model.live="country" class="input py-2 pr-8 sm:w-auto">
                <option value="">All countries</option>
                @foreach ($countries as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="sb-competition">Competition</label>
            <select id="sb-competition" wire:model.live="competition" class="input py-2 pr-8 sm:w-auto">
                <option value="">All competitions</option>
                @foreach ($competitions->when($country, fn ($c) => $c->where('country_id', $country)) as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Date strip --}}
    @if ($tab !== 'live')
        <div class="card flex min-w-0 items-center gap-1 p-1.5">
            <button type="button" wire:click="shiftDate(-1)" class="grid size-9 shrink-0 place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Previous day">
                <x-svg-icon name="chevron-left" class="size-4" />
            </button>
            <div class="flex min-w-0 flex-1 justify-between gap-1 overflow-x-auto scrollbar-none">
                @foreach ($days as $d)
                    @php $isSelected = $d->isSameDay($this->day()); @endphp
                    <button type="button" wire:click="$set('date', '{{ $d->isToday() ? '' : $d->toDateString() }}')"
                            class="min-w-16 flex-1 rounded-lg px-2 py-1.5 text-center transition {{ $isSelected ? 'bg-navy-900 text-white dark:bg-pitch-500' : 'hover:bg-slate-100 dark:hover:bg-white/10' }}"
                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                        <span class="block text-[11px] font-semibold uppercase {{ $isSelected ? 'text-white/80' : 'text-slate-500' }}">{{ $d->isToday() ? 'Today' : ($d->isYesterday() ? 'Yest.' : ($d->isTomorrow() ? 'Tom.' : $d->format('D'))) }}</span>
                        <span class="block text-sm font-bold">{{ $d->format('j M') }}</span>
                    </button>
                @endforeach
            </div>
            <button type="button" wire:click="shiftDate(1)" class="grid size-9 shrink-0 place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Next day">
                <x-svg-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    @endif

    <div wire:loading.delay.class="opacity-60" class="space-y-4 transition-opacity">
        @forelse ($this->groups as $competitionId => $fixtures)
            @php $comp = $fixtures->first()->competition; @endphp
            <section class="card overflow-hidden" wire:key="grp-{{ $competitionId }}" aria-label="{{ $comp->name }}">
                <a href="{{ $comp->url() }}" class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-2.5 hover:bg-slate-100 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10">
                    <span class="flex items-center gap-2">
                        @if ($comp->logo_url)<img src="{{ $comp->logo_url }}" alt="" class="size-5 object-contain" loading="lazy">@endif
                        <span class="text-sm font-bold text-navy-900 dark:text-white">{{ $comp->name }}</span>
                    </span>
                    <span class="text-xs font-semibold text-pitch-700 dark:text-pitch-400">Table & fixtures</span>
                </a>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($fixtures as $fixture)
                        <x-fixture-row :fixture="$fixture" wire:key="fx-{{ $fixture->id }}" />
                    @endforeach
                </div>
            </section>
        @empty
            <x-empty-state :title="$tab === 'live' ? 'No matches are live right now' : 'No matches found'" icon="calendar">
                {{ $tab === 'live' ? 'Live matches from the competitions we cover will appear here automatically.' : 'Try another date or remove a filter.' }}
            </x-empty-state>
        @endforelse
    </div>

    <p class="text-center text-xs text-slate-500">
        Kick-off times are shown in your local time.
        @if ($this->shouldPoll()) Scores refresh automatically every 30 seconds. @endif
    </p>
</div>
