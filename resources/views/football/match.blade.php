@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <h1 class="sr-only">{{ $fixture->homeTeam->name }} vs {{ $fixture->awayTeam->name }} — {{ $fixture->competition->name }}</h1>

        <div class="grid gap-8 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8">
                <livewire:football.match-centre :fixture="$fixture" />
            </div>
            <aside class="space-y-6 lg:col-span-4">
                @if ($articles->isNotEmpty())
                    <div class="card p-5">
                        <h2 class="section-title mb-4">Match coverage</h2>
                        <div class="space-y-4">
                            @foreach ($articles as $article)
                                <x-article-card :article="$article" variant="compact" />
                            @endforeach
                        </div>
                    </div>
                @endif
                <x-ad-slot placement="sidebar" />
                <div class="card p-5">
                    <h2 class="mb-3 font-display font-bold text-navy-900 dark:text-white">{{ $fixture->competition->name }}</h2>
                    <div class="flex flex-col gap-2 text-sm">
                        <a href="{{ $fixture->competition->url() }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">Competition overview</a>
                        @if ($fixture->competition->has_standings)
                            <a href="{{ route('football.competitions.table', $fixture->competition->slug) }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">League table</a>
                        @endif
                        <a href="{{ route('football.competitions.fixtures', $fixture->competition->slug) }}" class="font-semibold text-pitch-700 hover:underline dark:text-pitch-400">All fixtures & results</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
