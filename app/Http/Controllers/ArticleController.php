<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\Content\ContentQuery;
use App\Services\Content\ViewRecorder;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(string $slug, Request $request, ContentQuery $content, ViewRecorder $views, Seo $seo): View
    {
        $article = Article::query()->published()->where('slug', $slug)
            ->with(['category.parent', 'subcategory.parent', 'author', 'featuredMedia', 'ogMedia', 'tags', 'images', 'video', 'fixture.homeTeam', 'fixture.awayTeam', 'fixture.competition'])
            ->firstOrFail();

        $views->record($article, $request);

        return $this->render($article, $content, $seo);
    }

    /** Signed preview link for staff (works for drafts and scheduled articles). */
    public function preview(Article $article, ContentQuery $content, Seo $seo): View
    {
        Gate::authorize('view', $article);

        $article->load(['category.parent', 'subcategory.parent', 'author', 'featuredMedia', 'ogMedia', 'tags', 'images', 'video']);
        $seo->noindex = true;

        return $this->render($article, $content, $seo, preview: true);
    }

    public function latest(Seo $seo): View
    {
        $seo->set('Latest sports news', 'The latest Ghanaian and international sports news from SportyCiti.', route('articles.latest'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Latest');

        return view('pages.listing', [
            'heading' => 'Latest news',
            'description' => 'Every story, newest first.',
            'articles' => Article::query()->published()->forCard()->latest('published_at')->paginate(20),
        ]);
    }

    private function render(Article $article, ContentQuery $content, Seo $seo, bool $preview = false): View
    {
        $display = $article->displayCategory();
        $image = $article->socialImage();

        $seo->set($article->metaTitle(), $article->metaDescription(), $article->canonical_url ?: $article->url(), $image?->url('lg'));
        $seo->type = 'article';
        $seo->noindex = $seo->noindex || $article->noindex;
        $seo->properties = array_filter([
            'article:published_time' => $article->published_at?->toIso8601String(),
            'article:modified_time' => $article->updated_at?->toIso8601String(),
            'article:section' => $article->category?->name,
            'article:author' => $article->author?->name,
        ]);

        $seo->breadcrumb('Home', route('home'));
        foreach ($display?->lineage() ?? [] as $category) {
            $seo->breadcrumb($category->name, $category->url());
        }
        $seo->breadcrumb($article->title);

        $seo->schema(array_filter([
            '@context' => 'https://schema.org',
            '@type' => $article->type?->value === 'opinion' ? 'OpinionNewsArticle' : ($article->type?->value === 'insight' ? 'AnalysisNewsArticle' : 'NewsArticle'),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $article->url()],
            'headline' => mb_substr($article->title, 0, 110),
            'description' => $article->metaDescription(),
            'image' => $image ? [$image->url('lg')] : null,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'author' => $article->author ? array_filter(['@type' => 'Person', 'name' => $article->author->name, 'url' => $article->author->url()]) : null,
            'publisher' => [
                '@type' => 'NewsMediaOrganization',
                'name' => config('app.name'),
                'logo' => ['@type' => 'ImageObject', 'url' => asset('images/sportyciti-logo.png')],
            ],
            'articleSection' => $article->category?->name,
            'keywords' => $article->tags->pluck('name')->implode(', ') ?: null,
            'isAccessibleForFree' => true,
        ]));

        $related = $content->related($article);
        [$previous, $next] = $article->published_at ? $content->previousNext($article) : [null, null];

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
            'recommended' => $content->recommended($article, 4, $related->pluck('id')->all()),
            'previous' => $previous,
            'next' => $next,
            'preview' => $preview,
        ]);
    }
}
