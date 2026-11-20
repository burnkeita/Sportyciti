{{-- API-backed: form is computed from stored provider results for the linked fixture's teams. --}}
@php
    $fixture = $article->fixture;
    $query = app(\App\Services\SportsData\FootballQuery::class);
@endphp
@if ($fixture)
    <section class="card p-5" aria-label="Recent form">
        <h3 class="mb-4 flex items-center gap-2 font-display text-base font-bold text-navy-900 dark:text-white"><x-svg-icon name="chart" class="size-4 text-flame-500" /> {{ $data['title'] ?? 'Recent form' }}</h3>
        <div class="space-y-3">
            @foreach ([$fixture->homeTeam, $fixture->awayTeam] as $team)
                @php $form = $query->recentForm($team, (int) ($data['matches'] ?? 5), $fixture->kickoff_at); @endphp
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2 text-sm font-semibold"><x-team-logo :team="$team" /> {{ $team->name }}</span>
                    <span class="flex gap-1">
                        @forelse ($form->reverse() as $past)
                            <x-form-badge :result="$past->resultFor($team->id)" />
                        @empty
                            <span class="text-xs text-slate-400">No data</span>
                        @endforelse
                    </span>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-slate-400">Source: {{ $fixture->isDemo() ? 'demonstration data' : 'licensed sports data provider' }}. Oldest to most recent, left to right.</p>
    </section>
@endif
