<?php

namespace App\Http\Controllers;

use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Models\Article;
use App\Models\Category;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug, Request $request, Seo $seo): View
    {
        $category = Category::query()->active()->where('slug', $slug)
            ->with(['parent', 'children' => fn ($q) => $q->active()])
            ->firstOrFail();

        if ($category->slug === 'insights') {
            return $this->insights($request, $seo);
        }

        $articles = Article::query()->published()->forCard()->inCategory($category)
            ->latest('published_at')->paginate(18)->withQueryString();

        $this->seo($seo, $category, $articles->currentPage());

        return view('categories.show', [
            'category' => $category,
            'lead' => $articles->onFirstPage() ? $articles->getCollection()->take(3) : collect(),
            'articles' => $articles,
            'subcategories' => $category->parent_id ? $category->parent->children()->active()->get() : $category->children,
        ]);
    }

    /** SportyCiti Insights hub with insight-type filtering. */
    public function insights(Request $request, Seo $seo): View
    {
        $category = Category::where('slug', 'insights')->with('children')->first();
        $type = InsightType::tryFrom((string) $request->query('type'));

        $articles = Article::query()->published()->forCard()
            ->where(fn ($q) => $q->where('type', ArticleType::Insight)
                ->when($category, fn ($q) => $q->orWhere(fn ($q) => $q->inCategory($category))))
            ->when($type, fn ($q) => $q->where('insight_type', $type))
            ->latest('published_at')->paginate(15)->withQueryString();

        $seo->set(
            'SportyCiti Insights — football analysis, tactics and statistics',
            'Tactical breakdowns, match previews, post-match analysis, player and team form, and data-led football insight from SportyCiti.',
            route('insights.index'),
        );
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Insights');

        return view('categories.insights', [
            'category' => $category,
            'articles' => $articles,
            'activeType' => $type,
            'types' => InsightType::cases(),
        ]);
    }

    private function seo(Seo $seo, Category $category, int $page): void
    {
        $seo->set(
            ($category->seo_title ?: $category->name.' news').($page > 1 ? " — page {$page}" : ''),
            $category->meta_description ?: ($category->description ?: "Latest {$category->name} news, analysis and updates from SportyCiti."),
            $category->url().($page > 1 ? "?page={$page}" : ''),
        );

        $seo->breadcrumb('Home', route('home'));
        foreach ($category->lineage() as $node) {
            $seo->breadcrumb($node->name, $node->is($category) ? null : $node->url());
        }
    }
}
