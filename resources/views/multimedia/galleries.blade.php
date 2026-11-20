@extends('layouts.app')

@section('content')
    <div class="container-page pt-6">
        <x-breadcrumbs class="mb-4" />
        <h1 class="font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">Photo galleries</h1>

        @if ($galleries->isEmpty())
            <x-empty-state class="mt-8" title="No galleries yet" icon="camera" />
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($galleries as $gallery)
                    <article class="group relative">
                        <x-media-image :media="$gallery->coverMedia()" ratio="aspect-[4/3]" class="rounded-xl" :label="$gallery->title">
                            <span class="absolute bottom-3 right-3 inline-flex items-center gap-1 rounded bg-black/70 px-2 py-1 text-xs font-semibold text-white"><x-svg-icon name="camera" class="size-3.5" /> {{ $gallery->items_count }}</span>
                        </x-media-image>
                        <h2 class="mt-3 font-display text-lg font-bold text-navy-900 dark:text-white">
                            <a href="{{ $gallery->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $gallery->title }}</span></a>
                        </h2>
                        <p class="mt-1 text-xs text-slate-500"><x-local-time :at="$gallery->published_at" format="date" /></p>
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $galleries->links() }}</div>
        @endif
    </div>
@endsection
