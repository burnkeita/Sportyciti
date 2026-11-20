<?php

namespace App\Http\Controllers;

use App\Services\Content\ContentQuery;
use App\Support\Seo;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(ContentQuery $content, Seo $seo): View
    {
        $seo->set('', null, route('home'));
        $seo->schema(Seo::organisationSchema());
        $seo->schema([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => route('home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('search').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ]);

        return view('pages.home', ['sections' => $content->homepage()]);
    }
}
