<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Support\Seo;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(Seo $seo): View
    {
        $authors = User::query()->where('is_public_author', true)->where('is_active', true)
            ->withCount('publishedArticles')->orderByDesc('published_articles_count')->get();

        $seo->set('Our editorial team', 'Meet the journalists, editors and analysts behind SportyCiti.', route('authors.index'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Editorial team');

        return view('authors.index', ['authors' => $authors]);
    }

    public function show(string $slug, Seo $seo): View
    {
        $author = User::query()->where('slug', $slug)->where('is_public_author', true)->firstOrFail();

        $articles = Article::query()->published()->forCard()->where('author_id', $author->id)
            ->latest('published_at')->paginate(12);

        $seo->set($author->name, $author->bio ? str($author->bio)->limit(155)->toString() : "Articles by {$author->name} on SportyCiti.", $author->url(), $author->avatarUrl());
        $seo->type = 'profile';
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Editorial team', route('authors.index'))->breadcrumb($author->name);
        $seo->schema(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $author->name,
            'jobTitle' => $author->job_title,
            'description' => $author->bio,
            'image' => $author->avatarUrl(),
            'url' => $author->url(),
            'sameAs' => array_values(array_filter($author->social_links ?? [])) ?: null,
            'worksFor' => ['@type' => 'NewsMediaOrganization', 'name' => config('app.name')],
        ]));

        return view('authors.show', ['author' => $author, 'articles' => $articles]);
    }
}
