@extends('layouts.app')

@php
    $display = $article->displayCategory();
    $media = $article->featuredMedia;
    // Split the body after the third paragraph to place an in-article ad.
    $parts = preg_split('~(?<=</p>)~i', (string) $article->body, 4);
    $intro = implode('', array_slice($parts, 0, 3));
    $rest = $parts[3] ?? '';
@endphp

@section('content')
    @if ($preview)
        <div class="bg-flame-500 py-2 text-center text-sm font-bold text-white">
            Preview — status: {{ $article->status->getLabel() }}. This page is not public.
        </div>
    @endif

    <article class="pt-6">
        <header class="container-page max-w-4xl">
            <x-breadcrumbs class="mb-4" />

            <div class="mb-4 flex flex-wrap items-center gap-2">
                @if ($article->is_breaking)<span class="chip bg-flame-500 text-white">Breaking</span>@endif
                @if ($article->type?->value === 'insight')
                    <a href="{{ route('insights.index', ['type' => $article->insight_type?->value]) }}" class="chip bg-flame-100 text-flame-800 dark:bg-flame-500/15 dark:text-flame-300">{{ $article->insight_type?->getLabel() ?? 'Insight' }}</a>
                @elseif ($article->type && $article->type->value !== 'news')
                    <span class="chip bg-navy-100 text-navy-800 dark:bg-white/10 dark:text-white">{{ $article->type->getLabel() }}</span>
                @endif
                <x-category-chip :category="$display" />
            </div>

            @if ($article->is_sponsored)
                <p class="mb-4 rounded-lg border border-flame-200 bg-flame-50 px-4 py-2 text-sm text-flame-900 dark:border-flame-500/30 dark:bg-flame-500/10 dark:text-flame-100">
                    <strong>Sponsored content</strong>@if ($article->sponsor_name) — produced in partnership with
                        @if ($article->sponsor_url)<a href="{{ $article->sponsor_url }}" rel="sponsored noopener" target="_blank" class="font-semibold underline">{{ $article->sponsor_name }}</a>@else{{ $article->sponsor_name }}@endif.
                    @endif This is a paid partnership and is not independent SportyCiti journalism.
                </p>
            @endif

            <h1 class="font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl lg:text-5xl dark:text-white">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="mt-4 text-lg leading-relaxed text-slate-600 sm:text-xl dark:text-slate-300">{{ $article->excerpt }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-y border-slate-200 py-4 dark:border-white/10">
                <div class="flex items-center gap-3">
                    @if ($article->author)
                        @if ($article->author->avatarUrl())
                            <img src="{{ $article->author->avatarUrl() }}" alt="" class="size-11 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="grid size-11 place-items-center rounded-full bg-navy-900 text-sm font-bold text-white">{{ $article->author->initials() }}</span>
                        @endif
                    @endif
                    <div class="text-sm">
                        @if ($article->author)
                            <p class="font-semibold text-navy-900 dark:text-white">
                                By @if ($article->author->url())<a href="{{ $article->author->url() }}" class="hover:underline">{{ $article->author->name }}</a>@else{{ $article->author->name }}@endif
                                @if ($article->author->job_title)<span class="font-normal text-slate-500">· {{ $article->author->job_title }}</span>@endif
                            </p>
                        @endif
                        <p class="text-slate-500 dark:text-slate-400">
                            <x-local-time :at="$article->published_at" format="datetime" />
                            @if ($article->wasUpdatedAfterPublishing())
                                · Updated <x-local-time :at="$article->updated_at" format="datetime" />
                            @endif
                            · {{ $article->reading_time }} min read
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @unless ($preview)
                        <livewire:bookmark-button :article-id="$article->id" />
                    @endunless
                    <x-share-buttons :title="$article->title" :url="$article->url()" />
                </div>
            </div>
        </header>

        @if ($media || $article->video)
            <figure class="container-page mt-6 max-w-5xl">
                @if ($article->video?->embedUrl() && ! $media)
                    <div class="aspect-video overflow-hidden rounded-2xl bg-black">
                        <iframe src="{{ $article->video->embedUrl() }}" title="{{ $article->video->title }}" class="size-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" loading="lazy" allowfullscreen></iframe>
                    </div>
                @else
                    <x-media-image :media="$media" size="lg" sizes="(min-width: 1024px) 1024px, 100vw" eager class="rounded-2xl" :label="$article->title" />
                    @if ($media?->caption || $media?->credit)
                        <figcaption class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ $media->caption }} @if ($media->credit)<span class="text-xs uppercase tracking-wide">Photo: {{ $media->credit }}</span>@endif
                        </figcaption>
                    @endif
                @endif
            </figure>
        @endif

        <div class="container-page mt-10 grid gap-10 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8 lg:pl-8 xl:pl-16">
                <div class="article-body">
                    {!! $intro !!}
                    @if ($rest !== '')
                        <x-ad-slot placement="in_article" class="not-prose my-8" />
                        {!! $rest !!}
                    @endif
                </div>

                @if (! empty($article->insight_blocks))
                    <div class="mt-10 space-y-8">
                        @foreach ($article->insight_blocks as $block)
                            @includeIf('articles.blocks.'.$block['type'], ['data' => $block['data'] ?? [], 'article' => $article])
                        @endforeach
                    </div>
                @endif

                @if ($article->video && $media && $article->video->embedUrl())
                    <div class="mt-10">
                        <h2 class="section-title mb-4">Watch</h2>
                        <div class="aspect-video overflow-hidden rounded-xl bg-black">
                            <iframe src="{{ $article->video->embedUrl() }}" title="{{ $article->video->title }}" class="size-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" loading="lazy" allowfullscreen></iframe>
                        </div>
                    </div>
                @endif

                @if ($article->images->isNotEmpty())
                    <section class="mt-10" aria-label="Photos" x-data="{ open: null }">
                        <h2 class="section-title mb-4">Photos</h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($article->images as $image)
                                <button type="button" @click="open = {{ $loop->index }}" class="group overflow-hidden rounded-lg" aria-label="Enlarge photo {{ $loop->iteration }}">
                                    <x-media-image :media="$image" size="sm" sizes="33vw" ratio="aspect-square" />
                                </button>
                            @endforeach
                        </div>
                        <template x-teleport="body">
                            <div x-show="open !== null" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/90 p-4" @click.self="open = null" @keydown.escape.window="open = null" role="dialog" aria-modal="true">
                                @foreach ($article->images as $image)
                                    <figure x-show="open === {{ $loop->index }}" class="max-h-full max-w-5xl">
                                        <img src="{{ $image->url('lg') }}" alt="{{ $image->altText() }}" class="max-h-[80vh] rounded-lg object-contain">
                                        <figcaption class="mt-2 text-sm text-slate-300">{{ $image->caption }} @if ($image->credit) · Photo: {{ $image->credit }} @endif</figcaption>
                                    </figure>
                                @endforeach
                                <button type="button" @click="open = null" class="absolute right-4 top-4 grid size-10 place-items-center rounded-full bg-white/10 text-white" aria-label="Close"><x-svg-icon name="x" /></button>
                            </div>
                        </template>
                    </section>
                @endif

                @if ($article->tags->isNotEmpty())
                    <div class="mt-10 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tags</span>
                        @foreach ($article->tags as $tag)
                            <a href="{{ $tag->url() }}" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700 hover:bg-pitch-50 hover:text-pitch-700 dark:bg-white/10 dark:text-slate-200">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif

                <x-share-buttons :title="$article->title" :url="$article->url()" class="mt-8" />

                @if ($article->author && ($article->author->bio || $article->author->url()))
                    <aside class="card mt-10 flex gap-4 p-5" aria-label="About the author">
                        @if ($article->author->avatarUrl())
                            <img src="{{ $article->author->avatarUrl() }}" alt="" class="size-16 shrink-0 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="grid size-16 shrink-0 place-items-center rounded-full bg-navy-900 font-bold text-white">{{ $article->author->initials() }}</span>
                        @endif
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">About the author</p>
                            <p class="mt-1 font-display text-lg font-bold text-navy-900 dark:text-white">
                                @if ($article->author->url())<a href="{{ $article->author->url() }}" class="hover:underline">{{ $article->author->name }}</a>@else{{ $article->author->name }}@endif
                            </p>
                            @if ($article->author->specialisation)<p class="text-sm text-pitch-700 dark:text-pitch-400">{{ $article->author->specialisation }}</p>@endif
                            @if ($article->author->bio)<p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $article->author->bio }}</p>@endif
                        </div>
                    </aside>
                @endif

                @if ($previous || $next)
                    <nav class="mt-10 grid gap-4 sm:grid-cols-2" aria-label="More in {{ $article->category?->name }}">
                        @if ($previous)
                            <a href="{{ $previous->url() }}" class="card group p-4 hover:ring-pitch-300">
                                <span class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-slate-400"><x-svg-icon name="chevron-left" class="size-3" /> Previous</span>
                                <span class="mt-1 line-clamp-2 block font-semibold text-navy-900 group-hover:text-pitch-700 dark:text-white">{{ $previous->title }}</span>
                            </a>
                        @else
                            <span></span>
                        @endif
                        @if ($next)
                            <a href="{{ $next->url() }}" class="card group p-4 text-right hover:ring-pitch-300">
                                <span class="flex items-center justify-end gap-1 text-xs font-bold uppercase tracking-wider text-slate-400">Next <x-svg-icon name="chevron-right" class="size-3" /></span>
                                <span class="mt-1 line-clamp-2 block font-semibold text-navy-900 group-hover:text-pitch-700 dark:text-white">{{ $next->title }}</span>
                            </a>
                        @endif
                    </nav>
                @endif

                <x-ad-slot placement="article_bottom" class="mt-10" />

                @unless ($preview)
                    <div class="mt-12">
                        <livewire:comments :article-id="$article->id" lazy />
                    </div>
                @endunless
            </div>

            <aside class="space-y-8 lg:col-span-4">
                @if ($article->fixture)
                    <div class="card overflow-hidden">
                        <p class="border-b border-slate-100 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-white/5">Match centre</p>
                        <x-fixture-row :fixture="$article->fixture" show-competition show-date />
                    </div>
                @endif

                <div class="lg:sticky lg:top-24 space-y-8">
                    <x-ad-slot placement="sidebar" />

                    @if ($recommended->isNotEmpty())
                        <div class="card p-5">
                            <h2 class="section-title mb-4">Recommended</h2>
                            <div class="space-y-4">
                                @foreach ($recommended as $item)
                                    <x-article-card :article="$item" variant="compact" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="container-page mt-16" aria-labelledby="related-title">
            <x-section-header title="Related stories" id="related-title" />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-article-card :article="$item" :show-excerpt="false" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
