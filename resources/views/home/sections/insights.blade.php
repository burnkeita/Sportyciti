@if ($items->isNotEmpty())
    <section class="relative overflow-hidden bg-navy-900 py-12 text-white" aria-labelledby="sec-{{ $section->id }}">
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-pitch-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 size-96 rounded-full bg-flame-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="container-page relative">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-flame-400">Analysis · Tactics · Data</p>
                    <h2 id="sec-{{ $section->id }}" class="mt-1 font-display text-3xl font-extrabold">{{ $section->title }}</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="btn bg-white/10 text-white hover:bg-white/20">All insights <x-svg-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $article)
                    <article class="group relative flex flex-col">
                        <x-media-image :media="$article->featuredMedia" class="rounded-xl ring-1 ring-white/10" :label="$article->title" />
                        @if ($article->insight_type)
                            <span class="mt-4 text-[11px] font-bold uppercase tracking-wider text-flame-400">{{ $article->insight_type->getLabel() }}</span>
                        @endif
                        <h3 class="mt-1 font-display text-lg font-bold leading-snug">
                            <a href="{{ $article->url() }}" class="after:absolute after:inset-0"><span class="link-underline">{{ $article->title }}</span></a>
                        </h3>
                        <x-article-meta :article="$article" class="mt-2 text-slate-400" />
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
