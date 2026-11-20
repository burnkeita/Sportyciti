@extends('layouts.app')

@section('content')
    <div class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
        <div class="container-page py-8">
            <x-breadcrumbs class="mb-4" />
            <div class="flex flex-wrap items-center gap-5">
                <x-team-logo :team="$team" size="size-20" />
                <div>
                    <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">{{ $team->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ collect([$team->country?->name, $team->venue_name, $team->founded ? 'Founded '.$team->founded : null])->filter()->implode(' · ') }}
                    </p>
                    @if ($form->isNotEmpty())
                        <div class="mt-3 flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Form</span>
                            @foreach ($form->reverse() as $past)<x-form-badge :result="$past->resultFor($team->id)" />@endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <div class="space-y-10 lg:col-span-8">
            @if ($team->provider === 'demo')<x-demo-notice />@endif

            <section aria-labelledby="t-up">
                <x-section-header title="Upcoming fixtures" id="t-up" />
                @if ($upcoming->isEmpty())
                    <x-empty-state title="No upcoming fixtures" icon="calendar" />
                @else
                    <div class="card divide-y divide-slate-100 overflow-hidden dark:divide-white/5">
                        @foreach ($upcoming as $fixture)<x-fixture-row :fixture="$fixture" show-competition show-date />@endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="t-res">
                <x-section-header title="Recent results" id="t-res" />
                @if ($results->isEmpty())
                    <x-empty-state title="No results stored yet" icon="whistle" />
                @else
                    <div class="card divide-y divide-slate-100 overflow-hidden dark:divide-white/5">
                        @foreach ($results as $fixture)<x-fixture-row :fixture="$fixture" show-competition show-date />@endforeach
                    </div>
                @endif
            </section>

            @if ($news->isNotEmpty())
                <section aria-labelledby="t-news">
                    <x-section-header :title="$team->displayName().' news'" id="t-news" />
                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ($news as $article)<x-article-card :article="$article" :show-excerpt="false" />@endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:col-span-4">
            @foreach ($positions as $position)
                <div class="card p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $position->season->competition->name }}</p>
                    <div class="mt-2 flex items-end justify-between">
                        <p class="font-display text-4xl font-extrabold text-navy-900 dark:text-white">{{ $position->rank }}<span class="text-lg text-slate-400">{{ in_array($position->rank % 100, [11, 12, 13]) ? 'th' : (['th', 'st', 'nd', 'rd'][$position->rank % 10] ?? 'th') }}</span></p>
                        <p class="text-right text-sm text-slate-500">{{ $position->points }} pts · {{ $position->played }} played</p>
                    </div>
                    <a href="{{ route('football.competitions.table', $position->season->competition->slug) }}" class="mt-3 inline-block text-sm font-semibold text-pitch-700 hover:underline dark:text-pitch-400">View table</a>
                </div>
            @endforeach

            <div class="card p-5">
                <h2 class="section-title mb-4">Transfer activity</h2>
                @forelse ($transfers as $transfer)
                    <div class="border-b border-slate-100 py-3 last:border-0 dark:border-white/5">
                        <p class="text-sm font-semibold text-navy-900 dark:text-white">{{ $transfer->player_name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $transfer->to_team_id === $team->id ? 'In from '.($transfer->fromName() ?? 'unknown') : 'Out to '.($transfer->toName() ?? 'unknown') }}
                        </p>
                        <span class="chip mt-1 {{ $transfer->status->isConfirmed() ? 'bg-pitch-50 text-pitch-700 dark:bg-pitch-500/15 dark:text-pitch-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">{{ $transfer->status->getLabel() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No transfer activity recorded.</p>
                @endforelse
                <a href="{{ route('football.transfers.index', ['team' => $team->slug]) }}" class="mt-3 inline-block text-sm font-semibold text-pitch-700 hover:underline dark:text-pitch-400">All {{ $team->displayName() }} transfers</a>
            </div>
            <x-ad-slot placement="sidebar" />
        </aside>
    </div>
@endsection
