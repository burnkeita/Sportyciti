<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Setting;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    private const XML = ['Content-Type' => 'application/xml; charset=UTF-8'];

    /** Sitemap index: one sitemap per month of articles plus a static-pages sitemap. */
    public function sitemapIndex(): Response
    {
        $xml = Cache::remember('sitemap.index', 3600, function () {
            $driver = DB::connection()->getDriverName();
            $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', published_at) AS INTEGER)" : 'YEAR(published_at)';
            $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', published_at) AS INTEGER)" : 'MONTH(published_at)';

            $months = Article::query()->published()
                ->selectRaw("{$yearExpr} as y, {$monthExpr} as m, MAX(updated_at) as lastmod")
                ->groupBy('y', 'm')->orderByDesc('y')->orderByDesc('m')->get();

            return view('seo.sitemap-index', ['months' => $months])->render();
        });

        return response($xml, 200, self::XML);
    }

    public function articleSitemap(int $year, int $month): Response
    {
        $xml = Cache::remember("sitemap.articles.{$year}.{$month}", 3600, function () use ($year, $month) {
            $start = now()->setDate($year, $month, 1)->startOfDay();

            $articles = Article::query()->published()->where('noindex', false)
                ->whereBetween('published_at', [$start, $start->copy()->endOfMonth()])
                ->with('featuredMedia')
                ->select(['id', 'slug', 'title', 'updated_at', 'published_at', 'featured_media_id', 'canonical_url'])
                ->orderByDesc('published_at')->get()
                ->filter(fn ($a) => ! $a->canonical_url || $a->canonical_url === $a->url());

            return view('seo.sitemap-articles', ['articles' => $articles])->render();
        });

        return response($xml, 200, self::XML);
    }

    public function pagesSitemap(): Response
    {
        $xml = Cache::remember('sitemap.pages', 3600, fn () => view('seo.sitemap-pages', [
            'categories' => Category::active()->get(['slug', 'updated_at']),
            'competitions' => Competition::where('is_active', true)->get(['slug', 'updated_at']),
            'authors' => User::where('is_public_author', true)->whereNotNull('slug')->get(['slug', 'updated_at']),
            'videos' => Video::query()->published()->get(['slug', 'updated_at']),
        ])->render());

        return response($xml, 200, self::XML);
    }

    /** Google News sitemap: articles published in the last 48 hours (max 1,000). */
    public function newsSitemap(): Response
    {
        $xml = Cache::remember('sitemap.news', 300, fn () => view('seo.sitemap-news', [
            'publication' => Setting::get('seo.google_news_publication', config('app.name')),
            'articles' => Article::query()->published()->where('noindex', false)
                ->where('published_at', '>=', now()->subHours(48))
                ->with('tags:id,name')
                ->select(['id', 'slug', 'title', 'published_at'])
                ->latest('published_at')->limit(1000)->get(),
        ])->render());

        return response($xml, 200, self::XML);
    }

    public function robots(): Response
    {
        $lines = [];

        if (! Setting::get('seo.indexing_enabled', true) || ! app()->isProduction()) {
            $lines = ['User-agent: *', 'Disallow: /'];
        } else {
            $lines = [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /account',
                'Disallow: /preview',
                'Disallow: /search',
                'Disallow: /ad/',
                'Disallow: /livewire',
                'Allow: /',
            ];

            if ($extra = trim((string) Setting::get('seo.robots_extra', ''))) {
                $lines[] = $extra;
            }
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');
        $lines[] = 'Sitemap: '.route('sitemap.news');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function rss(): Response
    {
        $xml = Cache::remember('feed.rss', 600, fn () => view('seo.rss', [
            'articles' => Article::query()->published()->with(['author:id,name', 'category:id,name', 'featuredMedia'])
                ->latest('published_at')->limit(30)->get(),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
