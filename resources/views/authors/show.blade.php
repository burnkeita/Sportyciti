@extends('layouts.app')

@php
    $socialIcons = ['x' => 'x-social', 'facebook' => 'facebook', 'instagram' => 'instagram', 'youtube' => 'youtube', 'tiktok' => 'tiktok', 'linkedin' => 'link', 'website' => 'link'];
@endphp

@section('content')
    <div class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-navy-900">
        <div class="container-page py-10">
            <x-breadcrumbs class="mb-6" />
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                @if ($author->avatarUrl())
                    <img src="{{ $author->avatarUrl() }}" alt="{{ $author->name }}" class="size-28 rounded-full object-cover ring-4 ring-pitch-500/20">
                @else
                    <span class="grid size-28 place-items-center rounded-full bg-navy-900 font-display text-3xl font-extrabold text-white ring-4 ring-pitch-500/20">{{ $author->initials() }}</span>
                @endif
                <div>
                    <h1 class="font-display text-3xl font-extrabold text-navy-900 dark:text-white">{{ $author->name }}</h1>
                    @if ($author->job_title)<p class="mt-1 text-slate-600 dark:text-slate-300">{{ $author->job_title }}</p>@endif
                    @if ($author->specialisation)<p class="mt-1 text-sm font-semibold uppercase tracking-wide text-pitch-700 dark:text-pitch-400">{{ $author->specialisation }}</p>@endif
                    @if (! empty($author->social_links))
                        <div class="mt-3 flex gap-2">
                            @foreach (array_filter($author->social_links) as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener me" class="grid size-9 place-items-center rounded-full bg-slate-100 text-navy-900 hover:bg-pitch-500 hover:text-white dark:bg-white/10 dark:text-white" aria-label="{{ $author->name }} on {{ ucfirst($network) }}">
                                    <x-svg-icon :name="$socialIcons[$network] ?? 'link'" class="size-4" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @if ($author->bio)
                <p class="mt-6 max-w-3xl leading-relaxed text-slate-700 dark:text-slate-300">{{ $author->bio }}</p>
            @endif
        </div>
    </div>

    <div class="container-page pt-10">
        <x-section-header :title="'Latest from '.$author->name" as="h2" />
        @if ($articles->isEmpty())
            <x-empty-state title="No published articles yet" />
        @else
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
            <div class="mt-10">{{ $articles->links() }}</div>
        @endif
    </div>
@endsection
