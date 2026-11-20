@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <header class="mb-8 border-b border-slate-200 pb-6 dark:border-white/10">
            <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">{{ $heading }}</h1>
            @if (! empty($description))
                <p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-300">{{ $description }}</p>
            @endif
        </header>

        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($articles->isEmpty())
                    <x-empty-state title="No stories yet">New stories will appear here as soon as they are published.</x-empty-state>
                @else
                    <div class="space-y-6">
                        @foreach ($articles as $article)
                            <div class="{{ ! $loop->last ? 'border-b border-slate-200 pb-6 dark:border-white/10' : '' }}">
                                <x-article-card :article="$article" variant="horizontal" />
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-10">{{ $articles->links() }}</div>
                @endif
            </div>
            <aside class="space-y-8 lg:col-span-4">
                <div class="lg:sticky lg:top-24 space-y-8">
                    <x-ad-slot placement="sidebar" />
                    <div class="card p-5">
                        <h2 class="mb-2 font-display font-bold text-navy-900 dark:text-white">Never miss a story</h2>
                        <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">Get the SportyCiti briefing in your inbox.</p>
                        <livewire:newsletter-form source="sidebar" />
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
