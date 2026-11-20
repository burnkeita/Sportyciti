@props(['article', 'author' => true])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-slate-400']) }}>
    @if ($author && $article->author)
        <span class="font-semibold">{{ $article->author->name }}</span>
        <span aria-hidden="true">·</span>
    @endif
    @if ($article->published_at)
        <x-local-time :at="$article->published_at" format="relative" />
    @endif
    @if ($article->reading_time)
        <span aria-hidden="true">·</span>
        <span>{{ $article->reading_time }} min read</span>
    @endif
</div>
