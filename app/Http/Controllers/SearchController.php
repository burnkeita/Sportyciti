<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, Seo $seo): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:120'],
            'author' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => ['nullable', 'in:relevance,newest,popular'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $category = ! empty($filters['category']) ? Category::where('slug', $filters['category'])->first() : null;
        $sort = $filters['sort'] ?? ($q !== '' ? 'relevance' : 'newest');

        $hasCriteria = $q !== '' || $category || ! empty($filters['tag']) || ! empty($filters['author']) || ! empty($filters['from']);

        $articles = $hasCriteria
            ? Article::query()->published()->forCard()
                ->when($q !== '', fn ($query) => $query->search($q))
                ->when($category, fn ($query) => $query->inCategory($category))
                ->when($filters['tag'] ?? null, fn ($query, $slug) => $query->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
                ->when($filters['author'] ?? null, fn ($query, $slug) => $query->whereHas('author', fn ($a) => $a->where('slug', $slug)))
                ->when($filters['from'] ?? null, fn ($query, $from) => $query->where('published_at', '>=', $from))
                ->when($filters['to'] ?? null, fn ($query, $to) => $query->where('published_at', '<=', Carbon::parse($to)->endOfDay()))
                ->when($sort === 'newest' || ($sort === 'relevance' && $q === ''), fn ($query) => $query->latest('published_at'))
                ->when($sort === 'popular', fn ($query) => $query->orderByDesc('view_count'))
                ->paginate(15)->withQueryString()
            : null;

        $seo->set($q !== '' ? "Search results for “{$q}”" : 'Search', null, route('search'));
        $seo->noindex = true;
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Search');

        return view('pages.search', [
            'q' => $q,
            'filters' => $filters + ['sort' => $sort],
            'articles' => $articles,
            'categories' => Category::navigation(),
            'popularTags' => Tag::withCount('articles')->orderByDesc('articles_count')->limit(12)->get(),
            'authors' => User::where('is_public_author', true)->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }
}
