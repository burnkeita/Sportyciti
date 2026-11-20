@props(['article', 'variant' => 'default', 'eager' => false, 'showExcerpt' => null])
@php
    $category = $article->subcategory ?? $article->category;
    $showExcerpt ??= in_array($variant, ['default', 'hero', 'horizontal'], true);
    $isInsight = $article->type?->value === 'insight';
@endphp

@switch($variant)
    @case('hero')
        <article class="group relative isolate overflow-hidden rounded-2xl bg-navy-900 text-white shadow-lg">
            <x-media-image :media="$article->featuredMedia" size="lg" sizes="(min-width: 1024px) 66vw, 100vw" :eager="$eager" ratio="aspect-[4/3] sm:aspect-[16/9]" :label="$article->title" />
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/60 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    @if ($article->is_breaking)<span class="chip bg-flame-500 text-white">Breaking</span>@endif
                    @if ($isInsight)<span class="chip bg-flame-500/90 text-white">Insight</span>@endif
                    @if ($article->is_sponsored)<span class="chip bg-white text-navy-900">Sponsored</span>@endif
                    <x-category-chip :category="$category" dark />
                </div>
                <h2 class="font-display text-2xl font-extrabold leading-tight sm:text-4xl">
                    <a href="{{ $article->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $article->title }}</span></a>
                </h2>
                @if ($showExcerpt && $article->excerpt)
                    <p class="mt-3 hidden max-w-2xl text-base text-slate-200 sm:line-clamp-2">{{ $article->excerpt }}</p>
                @endif
                <x-article-meta :article="$article" class="mt-3 text-slate-300" />
            </div>
        </article>
        @break

    @case('overlay')
        <article class="group relative isolate overflow-hidden rounded-xl bg-navy-900 text-white">
            <x-media-image :media="$article->featuredMedia" size="md" sizes="(min-width: 1024px) 25vw, 50vw" ratio="aspect-[4/3]" :label="$article->title" />
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/50 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
                <x-category-chip :category="$category" dark class="mb-2" />
                <h3 class="font-display text-base font-bold leading-snug sm:text-lg">
                    <a href="{{ $article->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $article->title }}</span></a>
                </h3>
            </div>
        </article>
        @break

    @case('horizontal')
        <article class="group relative flex gap-4">
            <x-media-image :media="$article->featuredMedia" size="sm" sizes="160px" ratio="aspect-[4/3]" class="w-32 shrink-0 self-start rounded-lg sm:w-44" :label="$article->title" />
            <div class="min-w-0 flex-1">
                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                    <x-category-chip :category="$category" />
                    @if ($article->is_sponsored)<span class="chip bg-flame-50 text-flame-700 dark:bg-flame-500/15 dark:text-flame-300">Sponsored</span>@endif
                </div>
                <h3 class="font-display text-base font-bold leading-snug text-navy-900 sm:text-lg dark:text-white">
                    <a href="{{ $article->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $article->title }}</span></a>
                </h3>
                @if ($showExcerpt && $article->excerpt)
                    <p class="mt-1 hidden text-sm text-slate-600 sm:line-clamp-2 dark:text-slate-400">{{ $article->excerpt }}</p>
                @endif
                <x-article-meta :article="$article" class="mt-1.5" />
            </div>
        </article>
        @break

    @case('compact')
        <article class="group relative flex gap-3">
            <x-media-image :media="$article->featuredMedia" size="sm" sizes="96px" ratio="aspect-square" class="w-20 shrink-0 self-start rounded-lg" :label="$article->title" />
            <div class="min-w-0">
                <h3 class="line-clamp-3 text-sm font-semibold leading-snug text-navy-900 dark:text-white">
                    <a href="{{ $article->url() }}" class="after:absolute after:inset-0 group-hover:text-pitch-600 dark:group-hover:text-pitch-400">{{ $article->title }}</a>
                </h3>
                <x-article-meta :article="$article" :author="false" class="mt-1" />
            </div>
        </article>
        @break

    @case('text')
        <article class="group relative">
            <h3 class="text-[15px] font-semibold leading-snug text-navy-900 dark:text-white">
                <a href="{{ $article->url() }}" class="after:absolute after:inset-0 group-hover:text-pitch-600 dark:group-hover:text-pitch-400">{{ $article->title }}</a>
            </h3>
            <x-article-meta :article="$article" :author="false" class="mt-1" />
        </article>
        @break

    @default
        <article class="group relative flex flex-col">
            <x-media-image :media="$article->featuredMedia" size="md" :eager="$eager" class="rounded-xl" :label="$article->title">
                @if ($isInsight)
                    <span class="chip absolute left-3 top-3 bg-flame-500 text-white">Insight</span>
                @endif
            </x-media-image>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-category-chip :category="$category" />
                @if ($article->is_sponsored)<span class="chip bg-flame-50 text-flame-700 dark:bg-flame-500/15 dark:text-flame-300">Sponsored</span>@endif
            </div>
            <h3 class="mt-2 font-display text-lg font-bold leading-snug text-navy-900 dark:text-white">
                <a href="{{ $article->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $article->title }}</span></a>
            </h3>
            @if ($showExcerpt && $article->excerpt)
                <p class="mt-1.5 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">{{ $article->excerpt }}</p>
            @endif
            <x-article-meta :article="$article" class="mt-2" />
        </article>
@endswitch
