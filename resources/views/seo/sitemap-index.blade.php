{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ route('sitemap.pages') }}</loc>
    </sitemap>
@foreach ($months as $m)
    <sitemap>
        <loc>{{ route('sitemap.articles', ['year' => $m->y, 'month' => str_pad($m->m, 2, '0', STR_PAD_LEFT)]) }}</loc>
        <lastmod>{{ \Illuminate\Support\Carbon::parse($m->lastmod)->toAtomString() }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>
