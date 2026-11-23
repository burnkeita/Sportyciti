<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($articles as $article)
    <url>
        <loc>{{ $article->url() }}</loc>
        <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
@if ($article->featuredMedia)
        <image:image>
            <image:loc>{{ $article->featuredMedia->url('lg') }}</image:loc>
        </image:image>
@endif
    </url>
@endforeach
</urlset>
