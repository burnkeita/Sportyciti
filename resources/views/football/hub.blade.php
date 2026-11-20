@extends('layouts.app')

@section('content')
    @include('football.partials.nav', ['title' => 'Football'])

    <div class="container-page space-y-14 pt-8">
        <div class="grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($news->isNotEmpty())
                    <x-article-card :article="$news->first()" variant="hero" eager />
                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        @foreach ($news->skip(1)->take(4) as $article)
                            <x-article-card :article="$article" :show-excerpt="false" />
                        @endforeach
                    </div>
                @else
                    <x-empty-state title="Football news coming soon">Stories filed to football categories will appear here.</x-empty-state>
                @endif
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/10">
                        <h2 class="font-display text-sm font-extrabold uppercase tracking-wide text-navy-900 dark:text-white">Live & upcoming</h2>
                        <a href="{{ route('football.scores') }}" class="text-xs font-semibold text-pitch-700 dark:text-pitch-400">All scores</a>
                    </div>
                    <div class="p-0">
                        <livewire:football.live-widget :limit="8" />
                    </div>
                </div>
                <x-ad-slot placement="sidebar" />
            </aside>
        </div>

        @if ($competitions->isNotEmpty())
            <section aria-labelledby="comp-title">
                <x-section-header title="Competitions" :href="route('football.competitions.index')" id="comp-title" />
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($competitions as $competition)
                        <a href="{{ $competition->url() }}" class="card group flex items-center gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            @if ($competition->logo_url)
                                <img src="{{ $competition->logo_url }}" alt="" class="size-8 object-contain" loading="lazy">
                            @else
                                <span class="grid size-8 place-items-center rounded-lg bg-pitch-50 text-pitch-600 dark:bg-pitch-500/15"><x-svg-icon name="trophy" class="size-4" /></span>
                            @endif
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-navy-900 group-hover:text-pitch-700 dark:text-white">{{ $competition->displayName() }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ $competition->country?->name }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($news->count() > 5)
            <section aria-labelledby="more-title">
                <x-section-header title="More football news" :href="route('football.news')" id="more-title" />
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($news->skip(5) as $article)
                        <x-article-card :article="$article" variant="horizontal" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($insights->isNotEmpty())
            <section aria-labelledby="ins-title">
                <x-section-header title="Football insights" :href="route('football.insights')" id="ins-title" />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($insights as $article)
                        <x-article-card :article="$article" :show-excerpt="false" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($transfers->isNotEmpty())
            <section aria-labelledby="tr-title">
                <x-section-header title="Transfer centre" :href="route('football.transfers.index')" id="tr-title" />
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($transfers as $transfer)
                        @include('football.partials.transfer-card', ['transfer' => $transfer])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
