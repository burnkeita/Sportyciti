@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <header class="mb-10 max-w-2xl">
            <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">Our editorial team</h1>
            <p class="mt-3 text-slate-600 dark:text-slate-300">The journalists, editors and analysts who report, verify and explain Ghanaian and international sport for SportyCiti.</p>
        </header>

        @if ($authors->isEmpty())
            <x-empty-state title="Team profiles coming soon" icon="user" />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($authors as $author)
                    <a href="{{ $author->url() }}" class="card group flex gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                        @if ($author->avatarUrl())
                            <img src="{{ $author->avatarUrl() }}" alt="" class="size-16 shrink-0 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="grid size-16 shrink-0 place-items-center rounded-full bg-navy-900 text-lg font-bold text-white">{{ $author->initials() }}</span>
                        @endif
                        <div class="min-w-0">
                            <h2 class="font-display text-lg font-bold text-navy-900 group-hover:text-pitch-700 dark:text-white">{{ $author->name }}</h2>
                            @if ($author->job_title)<p class="text-sm text-slate-500">{{ $author->job_title }}</p>@endif
                            @if ($author->specialisation)<p class="mt-1 text-xs font-semibold uppercase tracking-wide text-pitch-700 dark:text-pitch-400">{{ $author->specialisation }}</p>@endif
                            <p class="mt-2 text-xs text-slate-400">{{ $author->published_articles_count }} {{ str('article')->plural($author->published_articles_count) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
