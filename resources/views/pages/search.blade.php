@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">Search</h1>

        <form action="{{ route('search') }}" method="get" role="search" class="card mt-6 grid gap-4 p-4 sm:p-5 lg:grid-cols-12" x-data="{ more: {{ collect($filters)->except(['q', 'sort'])->filter()->isNotEmpty() ? 'true' : 'false' }} }">
            <div class="lg:col-span-9">
                <label for="q" class="sr-only">Search terms</label>
                <input id="q" type="search" name="q" value="{{ $q }}" maxlength="120" placeholder="Search news, teams, players…" class="input text-base" autofocus>
            </div>
            <div class="flex gap-2 lg:col-span-3">
                <button class="btn-primary flex-1">Search</button>
                <button type="button" class="btn-ghost" @click="more = !more" :aria-expanded="more">Filters</button>
            </div>

            <div x-show="more" x-collapse class="grid gap-4 sm:grid-cols-2 lg:col-span-12 lg:grid-cols-5">
                <div>
                    <label class="label" for="f-category">Category</label>
                    <select id="f-category" name="category" class="input">
                        <option value="">Any</option>
                        @foreach ($categories as $root)
                            <optgroup label="{{ $root->name }}">
                                <option value="{{ $root->slug }}" @selected(($filters['category'] ?? '') === $root->slug)>All {{ $root->name }}</option>
                                @foreach ($root->children as $child)
                                    <option value="{{ $child->slug }}" @selected(($filters['category'] ?? '') === $child->slug)>{{ $child->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="f-author">Author</label>
                    <select id="f-author" name="author" class="input">
                        <option value="">Any</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->slug }}" @selected(($filters['author'] ?? '') === $author->slug)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="f-from">From</label>
                    <input id="f-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input">
                </div>
                <div>
                    <label class="label" for="f-to">To</label>
                    <input id="f-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input">
                </div>
                <div>
                    <label class="label" for="f-sort">Sort by</label>
                    <select id="f-sort" name="sort" class="input">
                        @foreach (['relevance' => 'Relevance', 'newest' => 'Newest', 'popular' => 'Most read'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if (! empty($filters['tag']))
                    <input type="hidden" name="tag" value="{{ $filters['tag'] }}">
                @endif
            </div>
        </form>

        @if ($errors->any())
            <p class="mt-4 text-sm text-red-600" role="alert">{{ $errors->first() }}</p>
        @endif

        <div class="mt-8 grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if ($articles === null)
                    <x-empty-state title="What are you looking for?" icon="search">Search across every SportyCiti story, or browse a popular tag.</x-empty-state>
                @elseif ($articles->isEmpty())
                    <x-empty-state title="No results found" icon="search">Try different keywords or remove some filters.</x-empty-state>
                @else
                    <p class="mb-6 text-sm text-slate-500" role="status">{{ number_format($articles->total()) }} {{ str('result')->plural($articles->total()) }}@if ($q !== '') for “{{ $q }}”@endif</p>
                    <div class="space-y-6">
                        @foreach ($articles as $article)
                            <x-article-card :article="$article" variant="horizontal" />
                        @endforeach
                    </div>
                    <div class="mt-10">{{ $articles->links() }}</div>
                @endif
            </div>
            <aside class="lg:col-span-4">
                @if ($popularTags->isNotEmpty())
                    <div class="card p-5">
                        <h2 class="mb-3 font-display font-bold text-navy-900 dark:text-white">Popular tags</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($popularTags as $tag)
                                <a href="{{ route('search', ['tag' => $tag->slug]) }}" class="rounded-full px-3 py-1 text-sm font-medium {{ ($filters['tag'] ?? '') === $tag->slug ? 'bg-pitch-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-pitch-50 dark:bg-white/10 dark:text-slate-200' }}">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
