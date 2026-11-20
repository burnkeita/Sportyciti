<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $category = ! empty($data['category']) ? Category::where('slug', $data['category'])->first() : null;

        $articles = Article::query()->published()->forCard()
            ->when($category, fn ($q) => $q->inCategory($category))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->search($term))
            ->latest('published_at')
            ->paginate($data['per_page'] ?? 20)->withQueryString();

        return ArticleResource::collection($articles);
    }

    public function show(string $slug): ArticleResource
    {
        $article = Article::query()->published()->where('slug', $slug)
            ->with(['category', 'subcategory', 'author', 'featuredMedia', 'tags'])->firstOrFail();

        return new ArticleResource($article, full: true);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => Category::navigation()->map(fn ($c) => [
            'name' => $c->name,
            'slug' => $c->slug,
            'children' => $c->children->map(fn ($child) => ['name' => $child->name, 'slug' => $child->slug])->values(),
        ])->values()]);
    }
}
