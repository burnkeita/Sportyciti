@if ($items->isNotEmpty())
    <section class="container-page" aria-labelledby="sec-{{ $section->id }}">
        <div class="grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <x-section-header :title="$section->title" :href="route('articles.latest')" id="sec-{{ $section->id }}" />
                <div class="grid gap-x-6 gap-y-8 sm:grid-cols-2">
                    @foreach ($items->take(4) as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
                @if ($items->count() > 4)
                    <div class="mt-8 grid gap-5 sm:grid-cols-2">
                        @foreach ($items->skip(4) as $article)
                            <x-article-card :article="$article" variant="compact" />
                        @endforeach
                    </div>
                @endif
            </div>
            <aside class="space-y-8 lg:col-span-4">
                <div class="card p-5">
                    <h2 class="font-display text-sm font-extrabold uppercase tracking-wide text-navy-900 dark:text-white">Football centre</h2>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        @foreach ([['Live scores', 'football.scores', 'ball'], ['Fixtures', 'football.fixtures', 'calendar'], ['Results', 'football.results', 'whistle'], ['Tables', 'football.competitions.index', 'table'], ['Transfers', 'football.transfers.index', 'swap'], ['Insights', 'insights.index', 'chart']] as [$label, $route, $icon])
                            <a href="{{ route($route) }}" class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2.5 text-sm font-semibold text-navy-900 transition hover:bg-pitch-50 hover:text-pitch-700 dark:bg-white/5 dark:text-white dark:hover:bg-pitch-500/15">
                                <x-svg-icon :name="$icon" class="size-4 text-pitch-500" /> {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <x-ad-slot placement="sidebar" />
            </aside>
        </div>
    </section>
@endif
