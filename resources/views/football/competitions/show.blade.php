@extends('layouts.app')

@section('content')
    @include('football.competitions.partials.header', ['active' => 'overview'])

    <div class="container-page grid gap-8 pt-8 lg:grid-cols-12">
        <div class="space-y-10 lg:col-span-8">
            @if ($isDemo)<x-demo-notice />@endif

            <section aria-labelledby="up-title">
                <x-section-header title="Upcoming & live" :href="route('football.competitions.fixtures', $competition->slug)" link-text="All fixtures" id="up-title" />
                @if ($upcoming->isEmpty())
                    <x-empty-state title="No upcoming fixtures stored" icon="calendar">Fixtures appear once they are published by our data provider.</x-empty-state>
                @else
                    <div class="card divide-y divide-slate-100 overflow-hidden dark:divide-white/5">
                        @foreach ($upcoming as $fixture)<x-fixture-row :fixture="$fixture" show-date />@endforeach
                    </div>
                @endif
            </section>

            <section aria-labelledby="res-title">
                <x-section-header title="Latest results" :href="route('football.competitions.fixtures', $competition->slug)" link-text="All results" id="res-title" />
                @if ($results->isEmpty())
                    <x-empty-state title="No results yet" icon="whistle" />
                @else
                    <div class="card divide-y divide-slate-100 overflow-hidden dark:divide-white/5">
                        @foreach ($results as $fixture)<x-fixture-row :fixture="$fixture" show-date />@endforeach
                    </div>
                @endif
            </section>

            @if ($news->isNotEmpty())
                <section aria-labelledby="news-title">
                    <x-section-header :title="$competition->displayName().' news'" :href="$competition->category?->url()" id="news-title" />
                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ($news as $article)<x-article-card :article="$article" :show-excerpt="false" />@endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:col-span-4">
            @if ($standings->isNotEmpty())
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="section-title">Table</h2>
                        <a href="{{ route('football.competitions.table', $competition->slug) }}" class="text-sm font-semibold text-pitch-700 dark:text-pitch-400">Full table</a>
                    </div>
                    @include('football.partials.standings-table', ['standings' => $standings, 'compact' => true, 'caption' => $competition->name.' table'])
                </div>
            @endif
            <x-ad-slot placement="sidebar" />
        </aside>
    </div>
@endsection
