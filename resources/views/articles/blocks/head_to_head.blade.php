{{-- API-backed head-to-head from stored results for the linked fixture. --}}
@php
    $fixture = $article->fixture;
    $meetings = $fixture ? app(\App\Services\SportsData\FootballQuery::class)->localHeadToHead($fixture, (int) ($data['matches'] ?? 6)) : collect();
@endphp
@if ($fixture && $meetings->isNotEmpty())
    <section class="card overflow-hidden" aria-label="Head-to-head">
        <h3 class="flex items-center gap-2 border-b border-slate-100 px-5 py-3 font-display text-base font-bold text-navy-900 dark:border-white/5 dark:text-white"><x-svg-icon name="swap" class="size-4 text-flame-500" /> {{ $data['title'] ?? 'Head-to-head' }}</h3>
        <div class="divide-y divide-slate-100 dark:divide-white/5">
            @foreach ($meetings as $m)
                <x-fixture-row :fixture="$m" show-competition show-date />
            @endforeach
        </div>
        <p class="border-t border-slate-100 px-5 py-2.5 text-xs text-slate-400 dark:border-white/5">Source: {{ $fixture->isDemo() ? 'demonstration data' : 'licensed sports data provider' }}.</p>
    </section>
@endif
