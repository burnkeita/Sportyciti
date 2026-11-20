@extends('layouts.app')

@section('content')
    <div class="container-page pt-6" x-data="{ open: null, total: {{ $gallery->items->count() }} }" @keydown.right.window="open !== null && (open = (open + 1) % total)" @keydown.left.window="open !== null && (open = (open - 1 + total) % total)">
        <x-breadcrumbs class="mb-4" />
        <header class="mb-8 max-w-3xl">
            <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">{{ $gallery->title }}</h1>
            @if ($gallery->description)<p class="mt-3 text-slate-600 dark:text-slate-300">{{ $gallery->description }}</p>@endif
            <p class="mt-2 text-sm text-slate-500"><x-local-time :at="$gallery->published_at" format="date" />@if ($gallery->photographer) · Photos: {{ $gallery->photographer }}@endif</p>
            <x-share-buttons :title="$gallery->title" :url="$gallery->url()" class="mt-4" />
        </header>

        <div class="columns-1 gap-4 sm:columns-2 lg:columns-3">
            @foreach ($gallery->items as $item)
                <figure class="mb-4 break-inside-avoid">
                    <button type="button" @click="open = {{ $loop->index }}" class="block w-full overflow-hidden rounded-xl" aria-label="View photo {{ $loop->iteration }} full size">
                        <img src="{{ $item->media->url('md') }}" @if ($item->media->srcset()) srcset="{{ $item->media->srcset() }}" sizes="(min-width: 1024px) 33vw, 100vw" @endif alt="{{ $item->media->altText() }}" loading="lazy" decoding="async" class="w-full transition hover:opacity-90">
                    </button>
                    @if ($item->caption || $item->media->caption)
                        <figcaption class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ $item->caption ?: $item->media->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        <template x-teleport="body">
            <div x-show="open !== null" x-cloak class="fixed inset-0 z-50 flex flex-col bg-black/95" role="dialog" aria-modal="true" aria-label="Photo viewer" @keydown.escape.window="open = null">
                <div class="flex items-center justify-between p-4 text-white">
                    <span class="text-sm tabular-nums" x-text="(open + 1) + ' / ' + total"></span>
                    <button type="button" @click="open = null" class="grid size-10 place-items-center rounded-full bg-white/10" aria-label="Close"><x-svg-icon name="x" /></button>
                </div>
                <div class="relative flex flex-1 items-center justify-center px-4 pb-8">
                    @foreach ($gallery->items as $item)
                        <figure x-show="open === {{ $loop->index }}" class="flex max-h-full flex-col items-center">
                            <img src="{{ $item->media->url('lg') }}" alt="{{ $item->media->altText() }}" class="max-h-[78vh] max-w-full rounded object-contain" loading="lazy">
                            <figcaption class="mt-3 max-w-2xl text-center text-sm text-slate-300">{{ $item->caption ?: $item->media->caption }} @if ($item->media->credit)<span class="text-slate-500">· {{ $item->media->credit }}</span>@endif</figcaption>
                        </figure>
                    @endforeach
                    <button type="button" @click="open = (open - 1 + total) % total" class="absolute left-4 grid size-12 place-items-center rounded-full bg-white/10 text-white" aria-label="Previous photo"><x-svg-icon name="chevron-left" /></button>
                    <button type="button" @click="open = (open + 1) % total" class="absolute right-4 grid size-12 place-items-center rounded-full bg-white/10 text-white" aria-label="Next photo"><x-svg-icon name="chevron-right" /></button>
                </div>
            </div>
        </template>
    </div>
@endsection
