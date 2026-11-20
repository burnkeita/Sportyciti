@extends('layouts.app')

@section('content')
    <div class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
        <div class="container-page py-8">
            <x-breadcrumbs class="mb-3" />
            <div class="flex items-center gap-3">
                <span class="h-8 w-1.5 rounded-full" style="background-color: {{ $category->color ?: '#12B76A' }}" aria-hidden="true"></span>
                <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">{{ $category->name }}</h1>
            </div>
            @if ($category->description)
                <p class="mt-3 max-w-3xl text-slate-600 dark:text-slate-300">{{ $category->description }}</p>
            @endif

            @if ($subcategories->isNotEmpty())
                <nav class="-mx-1 mt-5 flex gap-2 overflow-x-auto px-1 pb-1 scrollbar-none" aria-label="{{ $category->parent?->name ?? $category->name }} sections">
                    @if ($category->parent)
                        <a href="{{ $category->parent->url() }}" class="tab">All {{ $category->parent->name }}</a>
                    @else
                        <span class="tab tab-active">All</span>
                    @endif
                    @foreach ($subcategories as $sub)
                        <a href="{{ $sub->url() }}" class="tab {{ $sub->is($category) ? 'tab-active' : '' }}">{{ $sub->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    <div class="container-page pt-8">
        @if ($articles->isEmpty())
            <x-empty-state title="No stories in this section yet">Check back soon — our newsroom is on it.</x-empty-state>
        @else
            @if ($lead->isNotEmpty())
                <div class="mb-10 grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2"><x-article-card :article="$lead->first()" variant="hero" eager /></div>
                    <div class="grid gap-6">
                        @foreach ($lead->skip(1) as $article)
                            <x-article-card :article="$article" variant="overlay" />
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2">
                        @foreach ($articles->getCollection()->when($lead->isNotEmpty(), fn ($c) => $c->skip(3)) as $article)
                            <x-article-card :article="$article" />
                        @endforeach
                    </div>
                    <div class="mt-10">{{ $articles->links() }}</div>
                </div>
                <aside class="lg:col-span-4">
                    <div class="lg:sticky lg:top-24 space-y-8">
                        <x-ad-slot placement="sidebar" />
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
