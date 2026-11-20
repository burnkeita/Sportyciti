<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use App\Support\Seo;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(string $slug, Seo $seo): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $articles = Article::query()->published()->forCard()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->latest('published_at')->paginate(18);

        $seo->set("{$tag->name} — latest news", "All SportyCiti stories tagged {$tag->name}.", $tag->url());
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Tags')->breadcrumb($tag->name);
        $seo->noindex = $articles->total() < 2; // avoid thin tag pages in search

        return view('pages.listing', [
            'heading' => '#'.$tag->name,
            'description' => $articles->total().' '.str('story')->plural($articles->total()),
            'articles' => $articles,
        ]);
    }
}
