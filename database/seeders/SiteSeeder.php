<?php

namespace Database\Seeders;

use App\Models\AdPlacement;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site.name' => 'SportyCiti',
            'site.tagline' => 'Ghana & World Sports News, Scores and Insights',
            'site.description' => 'SportyCiti brings you breaking Ghanaian and international sports news, live football scores, fixtures, league tables, transfers and expert analysis.',
            'site.contact_email' => 'newsroom@sportyciti.com',
            'social.links' => ['facebook' => null, 'x' => null, 'instagram' => null, 'youtube' => null, 'tiktok' => null, 'whatsapp' => null],
            'seo.indexing_enabled' => true,
            'seo.robots_extra' => '',
            'seo.google_news_publication' => 'SportyCiti',
            'ads.enabled' => true,
            'ads.adsense_client' => null,
            'comments.enabled' => true,
            'comments.auto_approve_trusted' => false,
        ];

        foreach ($settings as $key => $value) {
            if (! Setting::where('key', $key)->exists()) {
                Setting::put($key, $value);
            }
        }

        $placements = [
            ['header', 'Header leaderboard', '728×90 / 970×90'],
            ['home_banner', 'Homepage banner', '970×250'],
            ['home_mid', 'Homepage mid-page banner', '970×250'],
            ['sidebar', 'Sidebar', '300×250 / 300×600'],
            ['in_article', 'In-article', '728×90 / 336×280'],
            ['article_bottom', 'Below article', '728×90'],
            ['mobile_banner', 'Mobile sticky banner', '320×50'],
            ['football_banner', 'Football section banner', '970×90'],
        ];

        foreach ($placements as [$key, $name, $size]) {
            AdPlacement::updateOrCreate(['key' => $key], ['name' => $name, 'size_hint' => $size, 'is_active' => true]);
        }

        if (HomepageSection::count() === 0) {
            $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');
            $sections = [
                ['Top stories', 'featured', null, 5],
                ['Latest headlines', 'latest', null, 8],
                ['Live scores & upcoming fixtures', 'scores', null, 8],
                ['Ghana Sports', 'category', $cat('ghana'), 6],
                ['Advertisement', 'ad', null, 1, ['placement' => 'home_banner']],
                ['International Sports', 'category', $cat('international'), 6],
                ['Football news', 'category', null, 6, ['football' => true]],
                ['SportyCiti Insights', 'insights', $cat('insights'), 4],
                ['Featured match previews', 'previews', $cat('match-previews'), 3],
                ['Transfer centre', 'transfers', null, 6],
                ['Trending & most read', 'trending', null, 6],
                ['Videos & multimedia', 'videos', null, 4],
                ['Other Sports', 'category', $cat('other-sports'), 4],
                ['Newsletter', 'newsletter', null, 1],
            ];

            foreach ($sections as $i => $s) {
                HomepageSection::create([
                    'title' => $s[0], 'type' => $s[1], 'category_id' => $s[2], 'item_limit' => $s[3],
                    'options' => $s[4] ?? null, 'position' => $i, 'is_active' => true,
                ]);
            }
        }
    }
}
