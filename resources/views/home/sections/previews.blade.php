@if ($items->isNotEmpty())
    <section class="container-page" aria-labelledby="sec-{{ $section->id }}">
        <x-section-header :title="$section->title" :href="$section->category?->url()" id="sec-{{ $section->id }}" />
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($items as $article)
                <x-article-card :article="$article" variant="overlay" />
            @endforeach
        </div>
    </section>
@endif
