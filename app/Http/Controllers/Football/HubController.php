<?php

namespace App\Http\Controllers\Football;

use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Transfer;
use App\Services\Content\ContentQuery;
use App\Services\SportsData\FootballQuery;
use App\Support\Seo;
use Illuminate\View\View;

class HubController extends Controller
{
    public function index(ContentQuery $content, FootballQuery $football, Seo $seo): View
    {
        $seo->set(
            'Football — live scores, fixtures, tables, transfers and news',
            'Live football scores, fixtures, results, league tables, transfer news and analysis covering the Ghana Premier League, Premier League, Champions League, AFCON and more.',
            route('football.index'),
        );
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football');

        $news = $content->football(9);

        return view('football.hub', [
            'news' => $news,
            'insights' => Article::query()->published()->forCard()->where('type', ArticleType::Insight)
                ->where(fn ($q) => $q->whereIn('category_id', Category::footballIds())->orWhereIn('subcategory_id', Category::footballIds()))
                ->latest('published_at')->limit(4)->get(),
            'competitions' => Competition::query()->active()->where('is_featured', true)->with('country')->get(),
            'transfers' => Transfer::query()->public()->with(['fromTeam', 'toTeam'])->latest('reported_at')->limit(6)->get(),
        ]);
    }

    public function scores(Seo $seo): View
    {
        $seo->set('Live football scores today', 'Live football scores, in-play match updates, goals, cards and results updated automatically.', route('football.scores'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Live scores');

        return view('football.scores');
    }

    public function fixtures(Seo $seo): View
    {
        $seo->set('Football fixtures', 'Upcoming football fixtures with kick-off times in your timezone. Filter by league, team and date.', route('football.fixtures'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Fixtures');

        return view('football.fixtures', ['mode' => 'fixtures']);
    }

    public function results(Seo $seo): View
    {
        $seo->set('Football results', 'Latest football results and match reports. Filter by league, team and date.', route('football.results'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Results');

        return view('football.fixtures', ['mode' => 'results']);
    }

    public function news(Seo $seo): View
    {
        $ids = Category::footballIds();
        $seo->set('Football news', 'Breaking football news from Ghana and around the world: clubs, national teams, interviews and match reports.', route('football.news'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('News');

        return view('pages.listing', [
            'heading' => 'Football news',
            'description' => 'Ghanaian and international football — clubs, national teams, interviews and match reports.',
            'articles' => Article::query()->published()->forCard()
                ->where(fn ($q) => $q->whereIn('category_id', $ids)->orWhereIn('subcategory_id', $ids))
                ->latest('published_at')->paginate(18),
        ]);
    }

    public function insights(Seo $seo): View
    {
        $ids = Category::footballIds();
        $seo->set('Football insights and analysis', 'Pre-match and post-match analysis, tactical breakdowns, head-to-head statistics and expert opinion.', route('football.insights'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Insights');

        return view('pages.listing', [
            'heading' => 'Football insights',
            'description' => 'Analysis, tactics, statistics and expert opinion.',
            'articles' => Article::query()->published()->forCard()->where('type', ArticleType::Insight)
                ->where(fn ($q) => $q->whereIn('category_id', $ids)->orWhereIn('subcategory_id', $ids))
                ->latest('published_at')->paginate(18),
        ]);
    }
}
