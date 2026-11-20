@if ($items->isNotEmpty())
    <section class="bg-navy-950 py-12 text-white" aria-labelledby="sec-{{ $section->id }}">
        <div class="container-page">
            <div class="mb-6 flex items-end justify-between gap-4 border-b border-white/10 pb-3">
                <h2 id="sec-{{ $section->id }}" class="section-title !text-white">{{ $section->title }}</h2>
                <a href="{{ route('videos.index') }}" class="text-sm font-semibold text-pitch-400 hover:text-pitch-300">All videos</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $video)
                    @include('multimedia.partials.video-card', ['video' => $video, 'dark' => true])
                @endforeach
            </div>
        </div>
    </section>
@endif
