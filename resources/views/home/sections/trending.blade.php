@php
    $lists = ['Trending now' => $items['trending'] ?? collect(), 'Most read' => $items['most_read'] ?? collect()];
@endphp
@if (collect($lists)->flatten()->isNotEmpty())
    <section class="container-page" aria-label="{{ $section->title }}">
        <div class="grid gap-8 md:grid-cols-2">
            @foreach ($lists as $heading => $list)
                <div class="card p-5 sm:p-6">
                    <h2 class="section-title mb-5">{{ $heading }}</h2>
                    <ol class="space-y-5">
                        @foreach ($list as $article)
                            <li class="flex gap-4">
                                <span class="font-display text-3xl font-extrabold leading-none text-pitch-500/40 tabular-nums" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <x-article-card :article="$article" variant="text" />
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endforeach
        </div>
    </section>
@endif
