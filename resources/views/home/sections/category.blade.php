@php
    $href = ($section->options['football'] ?? false) ? route('football.news') : $section->category?->url();
@endphp
@if ($items->isNotEmpty())
    <section class="container-page" aria-labelledby="sec-{{ $section->id }}">
        <x-section-header :title="$section->title" :href="$href" id="sec-{{ $section->id }}" />

        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <x-article-card :article="$items->first()" />
            </div>
            <div class="grid content-start gap-5 lg:col-span-7">
                @foreach ($items->skip(1) as $article)
                    <x-article-card :article="$article" variant="horizontal" :show-excerpt="$loop->first" />
                @endforeach
            </div>
        </div>
    </section>
@endif
