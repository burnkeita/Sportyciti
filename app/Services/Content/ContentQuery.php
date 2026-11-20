<?php

namespace App\Services\Content;

use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Transfer;
use App\Models\Video;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cached read queries for public editorial pages.
 */
class ContentQuery
{
    public function breaking(): Collection
    {
        return Cache::remember('articles.breaking', 120, fn () => Article::query()->breaking()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->latest('published_at')->limit(8)->get());
    }

    public function latest(int $limit = 10, array $exclude = []): Collection
    {
        return Article::query()->published()->forCard()
            ->when($exclude, fn ($q) => $q->whereNotIn('id', $exclude))
            ->latest('published_at')->limit($limit)->get();
    }

    public function inCategory(Category $category, int $limit = 6, array $exclude = []): Collection
    {
        return Article::query()->published()->forCard()->inCategory($category)
            ->when($exclude, fn ($q) => $q->whereNotIn('id', $exclude))
            ->latest('published_at')->limit($limit)->get();
    }

    public function football(int $limit = 6, array $exclude = []): Collection
    {
        $ids = Category::footballIds();

        return Article::query()->published()->forCard()
            ->where(fn ($q) => $q->whereIn('category_id', $ids)->orWhereIn('subcategory_id', $ids))
            ->when($exclude, fn ($q) => $q->whereNotIn('id', $exclude))
            ->latest('published_at')->limit($limit)->get();
    }

    public function insights(int $limit = 4, array $exclude = []): Collection
    {
        return Article::query()->published()->forCard()->where('type', ArticleType::Insight)
            ->when($exclude, fn ($q) => $q->whereNotIn('id', $exclude))
            ->latest('published_at')->limit($limit)->get();
    }

    public function featured(int $limit = 5): Collection
    {
        $featured = Article::query()->published()->forCard()->where('is_featured', true)
            ->latest('published_at')->limit($limit)->get();

        return $featured->count() >= $limit
            ? $featured
            : $featured->concat($this->latest($limit - $featured->count(), $featured->pluck('id')->all()));
    }

    /** Most viewed in the last 48 hours (falls back to recent all-time views). */
    public function trending(int $limit = 6): Collection
    {
        return Cache::remember("articles.trending.{$limit}", 600, function () use ($limit) {
            $ids = DB::table('article_views')
                ->where('date', '>=', now()->subDays(2)->toDateString())
                ->groupBy('article_id')->orderByRaw('SUM(views) DESC')
                ->limit($limit * 2)->pluck('article_id');

            $articles = Article::query()->published()->forCard()->whereIn('id', $ids)->get()
                ->sortBy(fn ($a) => $ids->search($a->id))->take($limit)->values();

            return $articles->count() >= $limit ? $articles : $articles->concat($this->mostRead($limit - $articles->count(), $articles->pluck('id')->all()));
        });
    }

    public function mostRead(int $limit = 6, array $exclude = []): Collection
    {
        return Article::query()->published()->forCard()
            ->where('published_at', '>=', now()->subDays(30))
            ->when($exclude, fn ($q) => $q->whereNotIn('id', $exclude))
            ->orderByDesc('view_count')->limit($limit)->get();
    }

    public function related(Article $article, int $limit = 4): Collection
    {
        $manual = $article->relatedArticles()->published()->forCard()->limit($limit)->get();

        if ($manual->count() >= $limit) {
            return $manual;
        }

        $tagIds = $article->tags->pluck('id');
        $exclude = $manual->pluck('id')->push($article->id)->all();

        $auto = Article::query()->published()->forCard()
            ->whereNotIn('id', $exclude)
            ->where(fn ($q) => $q
                ->when($article->subcategory_id, fn ($q) => $q->where('subcategory_id', $article->subcategory_id))
                ->orWhere('category_id', $article->category_id)
                ->when($tagIds->isNotEmpty(), fn ($q) => $q->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds))))
            ->where('published_at', '>=', now()->subMonths(6))
            ->latest('published_at')->limit($limit - $manual->count())->get();

        return $manual->concat($auto);
    }

    public function recommended(Article $article, int $limit = 4, array $exclude = []): Collection
    {
        return $this->trending($limit + 6)
            ->reject(fn ($a) => $a->id === $article->id || in_array($a->id, $exclude, true))
            ->take($limit)->values();
    }

    /** @return array{0: ?Article, 1: ?Article} */
    public function previousNext(Article $article): array
    {
        $base = fn () => Article::query()->published()->where('category_id', $article->category_id)->select(['id', 'title', 'slug', 'published_at']);

        return [
            $base()->where('published_at', '<', $article->published_at)->latest('published_at')->first(),
            $base()->where('published_at', '>', $article->published_at)->oldest('published_at')->first(),
        ];
    }

    /**
     * Resolve the data for every active homepage section, avoiding duplicate stories
     * across the editorial blocks.
     *
     * @return Collection<int, array{section: HomepageSection, items: mixed}>
     */
    public function homepage(): Collection
    {
        return Cache::remember('home.sections.data', 120, function () {
            $sections = HomepageSection::query()->where('is_active', true)->with('category')->orderBy('position')->get();
            $used = [];
            $out = collect();

            foreach ($sections as $section) {
                $limit = max(1, $section->item_limit);

                $items = match ($section->type) {
                    'featured' => $this->featured($limit),
                    'latest' => $this->latest($limit, $used),
                    'category' => ($section->options['football'] ?? false)
                        ? $this->football($limit, $used)
                        : ($section->category ? $this->inCategory($section->category, $limit, $used) : collect()),
                    'insights' => $this->insights($limit, $used),
                    'previews' => $section->category ? $this->inCategory($section->category, $limit, $used) : collect(),
                    'trending' => collect(['trending' => $this->trending($limit), 'most_read' => $this->mostRead($limit)]),
                    'videos' => Video::query()->published()->with('thumbnail')->latest('published_at')->limit($limit)->get(),
                    'transfers' => Transfer::query()->public()->with(['fromTeam', 'toTeam'])->latest('reported_at')->limit($limit)->get(),
                    'scores' => collect(), // rendered by the polling Livewire widget
                    default => collect(),
                };

                if ($items instanceof Collection && in_array($section->type, ['featured', 'latest', 'category', 'insights', 'previews'], true)) {
                    $used = array_merge($used, $items->pluck('id')->all());
                }

                $out->push(['section' => $section, 'items' => $items]);
            }

            return $out;
        });
    }
}
