@if ($items->isNotEmpty())
    <section class="container-page" aria-label="{{ $section->title }}">
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-article-card :article="$items->first()" variant="hero" eager />
            </div>
            <div class="card flex flex-col divide-y divide-slate-100 p-4 dark:divide-white/5">
                <h2 class="section-title pb-3 !text-sm">Top stories</h2>
                @foreach ($items->skip(1)->take(4) as $article)
                    <div class="py-3.5 last:pb-0">
                        <x-article-card :article="$article" variant="compact" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
