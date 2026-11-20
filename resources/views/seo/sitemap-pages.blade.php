{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ([route('home'), route('football.index'), route('football.scores'), route('football.fixtures'), route('football.results'), route('football.competitions.index'), route('football.transfers.index'), route('insights.index'), route('videos.index'), route('galleries.index'), route('authors.index'), route('pages.show', 'about'), route('pages.show', 'contact'), route('pages.show', 'editorial-policy')] as $url)
    <url><loc>{{ $url }}</loc></url>
@endforeach
@foreach ($categories as $category)
    <url><loc>{{ route('categories.show', $category->slug) }}</loc><lastmod>{{ $category->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
@foreach ($competitions as $competition)
    <url><loc>{{ route('football.competitions.show', $competition->slug) }}</loc></url>
@endforeach
@foreach ($authors as $author)
    <url><loc>{{ route('authors.show', $author->slug) }}</loc></url>
@endforeach
@foreach ($videos as $video)
    <url><loc>{{ route('videos.show', $video->slug) }}</loc><lastmod>{{ $video->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
