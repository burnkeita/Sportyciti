@extends('layouts.app')

@section('content')
    <div class="relative overflow-hidden bg-navy-900 text-white">
        <div class="pointer-events-none absolute -right-20 -top-20 size-96 rounded-full bg-flame-500/15 blur-3xl" aria-hidden="true"></div>
        <div class="container-page relative py-12">
            <x-breadcrumbs class="mb-4 [&_a]:text-slate-300 [&_span]:text-slate-200" />
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-flame-400">Analysis · Tactics · Data</p>
            <h1 class="mt-2 font-display text-4xl font-extrabold sm:text-5xl">SportyCiti Insights</h1>
            <p class="mt-3 max-w-2xl text-slate-300">Tactical breakdowns, match previews, post-match analysis and data-led storytelling. Every statistic we publish is sourced from our licensed data provider or verified by our editors.</p>

            <nav class="-mx-1 mt-8 flex gap-2 overflow-x-auto px-1 pb-1 scrollbar-none" aria-label="Insight types">
                <a href="{{ route('insights.index') }}" class="tab {{ ! $activeType ? 'bg-white !text-navy-900' : '!text-slate-200 hover:!bg-white/10' }}">All</a>
                @foreach ($types as $type)
                    <a href="{{ route('insights.index', ['type' => $type->value]) }}" class="tab {{ $activeType === $type ? 'bg-white !text-navy-900' : '!text-slate-200 hover:!bg-white/10' }}">{{ $type->getLabel() }}</a>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="container-page pt-10">
        @if ($articles->isEmpty())
            <x-empty-state title="No insights published yet" icon="chart">Our analysts are working on it.</x-empty-state>
        @else
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" :eager="$loop->first" />
                @endforeach
            </div>
            <div class="mt-10">{{ $articles->links() }}</div>
        @endif
    </div>
@endsection
