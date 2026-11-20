@php $dark = $dark ?? false; @endphp
<article class="group relative">
    <div class="relative aspect-video overflow-hidden rounded-xl bg-navy-800">
        @if ($thumb = $video->thumbnailUrl())
            <img src="{{ $thumb }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @endif
        <span class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></span>
        <span class="absolute left-3 top-3 chip bg-black/60 text-white backdrop-blur">{{ $video->typeLabel() }}</span>
        <span class="absolute bottom-3 left-3 grid size-11 place-items-center rounded-full bg-flame-500 text-white shadow-lg transition group-hover:scale-110"><x-svg-icon name="play" class="size-5 translate-x-px" /></span>
        @if ($video->duration_seconds)
            <span class="absolute bottom-3 right-3 rounded bg-black/70 px-1.5 py-0.5 text-xs font-semibold text-white tabular-nums">{{ gmdate($video->duration_seconds >= 3600 ? 'G:i:s' : 'i:s', $video->duration_seconds) }}</span>
        @endif
    </div>
    <h3 class="mt-3 font-display font-bold leading-snug {{ $dark ? 'text-white' : 'text-navy-900 dark:text-white' }}">
        <a href="{{ $video->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $video->title }}</span></a>
    </h3>
    <p class="mt-1 text-xs {{ $dark ? 'text-slate-400' : 'text-slate-500' }}"><x-local-time :at="$video->published_at" format="relative" /></p>
</article>
