@extends('layouts.app')

@section('content')
    <div class="bg-navy-950 text-white">
        <div class="container-page py-10">
            <x-breadcrumbs class="mb-4 [&_a]:text-slate-300 [&_span]:text-slate-200" />
            <h1 class="font-display text-3xl font-extrabold sm:text-4xl">Videos</h1>
            <p class="mt-2 max-w-2xl text-slate-300">Interviews, press conferences, match analysis and original SportyCiti programmes.</p>

            <nav class="-mx-1 mt-6 flex gap-2 overflow-x-auto px-1 pb-1 scrollbar-none" aria-label="Video types">
                <a href="{{ route('videos.index') }}" class="tab {{ ! $activeType ? 'bg-white !text-navy-900' : '!text-slate-200 hover:!bg-white/10' }}">All</a>
                @foreach ($types as $key => $label)
                    <a href="{{ route('videos.index', ['type' => $key]) }}" class="tab {{ $activeType === $key ? 'bg-white !text-navy-900' : '!text-slate-200 hover:!bg-white/10' }}">{{ $label }}</a>
                @endforeach
            </nav>

            @if ($featured)
                <a href="{{ $featured->url() }}" class="group mt-8 grid items-center gap-6 lg:grid-cols-2">
                    <div class="relative aspect-video overflow-hidden rounded-2xl bg-navy-800">
                        @if ($featured->thumbnailUrl())<img src="{{ $featured->thumbnailUrl() }}" alt="" class="size-full object-cover" fetchpriority="high">@endif
                        <span class="absolute inset-0 grid place-items-center"><span class="grid size-16 place-items-center rounded-full bg-flame-500 shadow-xl transition group-hover:scale-110"><x-svg-icon name="play" class="size-7 translate-x-0.5" /></span></span>
                    </div>
                    <div>
                        <span class="chip bg-flame-500 text-white">Featured</span>
                        <h2 class="mt-3 font-display text-2xl font-extrabold group-hover:underline sm:text-3xl">{{ $featured->title }}</h2>
                        @if ($featured->description)<p class="mt-2 line-clamp-3 text-slate-300">{{ $featured->description }}</p>@endif
                    </div>
                </a>
            @endif
        </div>
    </div>

    <div class="container-page pt-10">
        @if ($videos->isEmpty())
            <x-empty-state title="No videos yet" icon="video" />
        @else
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($videos as $video)
                    @include('multimedia.partials.video-card', ['video' => $video])
                @endforeach
            </div>
            <div class="mt-10">{{ $videos->links() }}</div>
        @endif
    </div>
@endsection
