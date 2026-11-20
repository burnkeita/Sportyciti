{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>Ghana &amp; world sports news, live scores and insights</description>
        <language>en</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ $article->url() }}</link>
            <guid isPermaLink="true">{{ $article->url() }}</guid>
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
@if ($article->author)
            <author>{{ $article->author->name }}</author>
@endif
@if ($article->category)
            <category>{{ $article->category->name }}</category>
@endif
            <description>{{ $article->metaDescription() }}</description>
@if ($article->featuredMedia)
            <media:content url="{{ $article->featuredMedia->url('lg') }}" medium="image" />
@endif
        </item>
@endforeach
    </channel>
</rss>
