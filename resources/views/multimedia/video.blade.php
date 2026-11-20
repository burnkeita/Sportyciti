@extends('layouts.app')

@section('content')
    <div class="bg-navy-950 text-white">
        <div class="container-page max-w-5xl py-8">
            <x-breadcrumbs class="mb-4 [&_a]:text-slate-300 [&_span]:text-slate-200" />
            <div class="aspect-video overflow-hidden rounded-2xl bg-black shadow-2xl">
                @if ($video->embedUrl())
                    <iframe src="{{ $video->embedUrl() }}?rel=0" title="{{ $video->title }}" class="size-full" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
                @elseif ($video->fileUrl())
                    <video src="{{ $video->fileUrl() }}" controls preload="metadata" class="size-full" @if ($video->thumbnailUrl()) poster="{{ $video->thumbnailUrl() }}" @endif></video>
                @endif
            </div>
            <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span class="chip bg-flame-500 text-white">{{ $video->typeLabel() }}</span>
                    <h1 class="mt-3 font-display text-2xl font-extrabold sm:text-3xl">{{ $video->title }}</h1>
                    <p class="mt-1 text-sm text-slate-400"><x-local-time :at="$video->published_at" format="date" />@if ($video->rights_holder) · © {{ $video->rights_holder }}@endif</p>
                </div>
                <x-share-buttons :title="$video->title" :url="$video->url()" class="[&_span]:text-slate-400" />
            </div>
            @if ($video->description)
                <p class="mt-4 max-w-3xl whitespace-pre-line text-slate-300">{{ $video->description }}</p>
            @endif
        </div>
    </div>

    @if ($more->isNotEmpty())
        <div class="container-page pt-10">
            <x-section-header title="More videos" :href="route('videos.index')" />
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($more as $item)
                    @include('multimedia.partials.video-card', ['video' => $item])
                @endforeach
            </div>
        </div>
    @endif
@endsection
