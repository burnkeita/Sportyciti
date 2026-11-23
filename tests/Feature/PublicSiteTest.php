<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Fixture;
use App\Models\Tag;
use App\Models\User;
use App\Services\SportsData\FootballSyncService;
use App\Services\SportsData\Providers\DemoProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        config(['sportsdata.demo_fallback' => true, 'sportsdata.provider' => 'demo']);

        $author = User::factory()->create(['name' => 'Ama Mensah', 'is_public_author' => true]);
        $sub = Category::where('slug', 'black-stars')->firstOrFail();

        $this->article = Article::factory()->published()->create([
            'title' => 'Black Stars name squad for qualifiers',
            'category_id' => $sub->parent_id,
            'subcategory_id' => $sub->id,
            'author_id' => $author->id,
        ]);
        $this->article->tags()->attach(Tag::create(['name' => 'Black Stars']));

        (new FootballSyncService(new DemoProvider))->syncDate(now());
    }

    public function test_public_pages_render(): void
    {
        $fixture = Fixture::first();

        $urls = [
            '/', '/latest', '/category/ghana', '/category/black-stars', '/insights', '/tag/black-stars',
            '/search', '/search?q=squad', '/team', '/author/ama-mensah', '/videos', '/galleries',
            '/football', '/football/live-scores', '/football/fixtures', '/football/results', '/football/news',
            '/football/insights', '/football/competitions', '/football/competitions/sportyciti-demo-league',
            '/football/competitions/sportyciti-demo-league/table', '/football/competitions/sportyciti-demo-league/fixtures',
            '/football/transfers', '/about', '/privacy', '/login', '/register',
            $this->article->url(), $fixture->url(), $fixture->homeTeam->url(),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_article_page_has_seo_metadata_and_structured_data(): void
    {
        $this->get($this->article->url())
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$this->article->url().'">', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('Ama Mensah');
    }

    public function test_unpublished_articles_are_not_public(): void
    {
        foreach ([ArticleStatus::Draft, ArticleStatus::InReview, ArticleStatus::Archived] as $status) {
            $article = Article::factory()->create(['status' => $status, 'category_id' => $this->article->category_id]);
            $this->get($article->url())->assertNotFound();
        }

        $scheduled = Article::factory()->create([
            'status' => ArticleStatus::Scheduled,
            'published_at' => now()->addDay(),
            'category_id' => $this->article->category_id,
        ]);
        $this->get($scheduled->url())->assertNotFound();
    }

    public function test_sponsored_articles_are_labelled(): void
    {
        $sponsored = Article::factory()->published()->create([
            'category_id' => $this->article->category_id,
            'is_sponsored' => true,
            'sponsor_name' => 'Acme Drinks',
        ]);

        $this->get($sponsored->url())->assertSee('Sponsored content')->assertSee('Acme Drinks');
    }

    public function test_demo_football_data_is_clearly_labelled(): void
    {
        $this->get('/football/live-scores')->assertSee('Demo data')->assertSee('fictional demonstration data', false);
    }

    public function test_search_finds_published_articles_only(): void
    {
        Article::factory()->create(['title' => 'Secret squad draft', 'category_id' => $this->article->category_id]);

        $this->get('/search?q=squad')
            ->assertSee('Black Stars name squad for qualifiers')
            ->assertDontSee('Secret squad draft');
    }

    public function test_xml_feeds_start_with_declaration_and_templates_avoid_short_open_tags(): void
    {
        foreach (['/sitemap.xml', '/sitemaps/pages.xml', '/news-sitemap.xml', '/feed'] as $url) {
            $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $this->get($url)->assertOk()->getContent(), $url);
        }

        // "<?xml" inside a Blade template breaks on hosts with short_open_tag enabled.
        foreach (glob(resource_path('views/seo/*.blade.php')) as $template) {
            $this->assertStringNotContainsString('<?', file_get_contents($template), basename($template));
        }
    }

    public function test_seo_endpoints(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->get('/sitemaps/pages.xml')->assertOk()->assertSee(route('football.index'));
        $this->get('/news-sitemap.xml')->assertOk()->assertSee($this->article->url())->assertSee('<news:publication>', false);
        $this->get('/feed')->assertOk()->assertSee($this->article->title);

        $month = $this->article->published_at;
        $this->get(route('sitemap.articles', ['year' => $month->year, 'month' => $month->format('m')]))->assertOk()->assertSee($this->article->url());

        // Outside production, robots.txt blocks crawling.
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /')->assertSee('Sitemap:');
    }

    public function test_read_only_api(): void
    {
        $this->getJson('/api/v1/articles')->assertOk()->assertJsonPath('data.0.slug', $this->article->slug);
        $this->getJson('/api/v1/articles/'.$this->article->slug)->assertOk()->assertJsonStructure(['data' => ['title', 'body', 'tags']]);
        $this->getJson('/api/v1/football/fixtures')->assertOk()->assertJsonPath('demo', true);
        $this->getJson('/api/v1/articles/not-a-real-slug')->assertNotFound();
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
