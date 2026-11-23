<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach ($articles as $article)
    <url>
        <loc>{{ $article->url() }}</loc>
        <news:news>
            <news:publication>
                <news:name>{{ $publication }}</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>{{ $article->published_at->toAtomString() }}</news:publication_date>
            <news:title>{{ $article->title }}</news:title>
@if ($article->tags->isNotEmpty())
            <news:keywords>{{ $article->tags->pluck('name')->take(10)->implode(', ') }}</news:keywords>
@endif
        </news:news>
    </url>
@endforeach
</urlset>
