<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Services\Content\ContentQuery;
use App\Services\SportsData\FootballQuery;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(FootballQuery $football, Seo $seo): View
    {
        $seo->set('Football competitions', 'Leagues and cups covered by SportyCiti — fixtures, results and tables.', route('football.competitions.index'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Competitions');

        $competitions = Competition::query()->active()->with('country')
            ->where(fn ($q) => $q->where('provider', '!=', 'demo')->orWhere('provider', $football->activeProviderKey()))
            ->get()->groupBy(fn ($c) => $c->country?->name ?? 'International');

        return view('football.competitions.index', ['groups' => $competitions]);
    }

    public function show(string $slug, FootballQuery $football, ContentQuery $content, Seo $seo): View
    {
        $competition = $this->find($slug);
        $season = $football->currentSeason($competition);

        $this->seo($seo, $competition, 'fixtures, results, table and news');

        return view('football.competitions.show', [
            'competition' => $competition,
            'season' => $season,
            'standings' => $season && $competition->has_standings ? $football->standings($season) : collect(),
            'upcoming' => $football->baseFixtures()->where('competition_id', $competition->id)
                ->where(fn ($q) => $q->upcoming()->orWhere(fn ($q) => $q->live()))->orderBy('kickoff_at')->limit(10)->get(),
            'results' => $football->baseFixtures()->where('competition_id', $competition->id)->finished()
                ->orderByDesc('kickoff_at')->limit(10)->get(),
            'news' => $competition->category ? $content->inCategory($competition->category, 6) : collect(),
            'isDemo' => $competition->provider === 'demo',
        ]);
    }

    public function table(string $slug, Request $request, FootballQuery $football, Seo $seo): View
    {
        $competition = $this->find($slug);
        abort_unless($competition->has_standings, 404);

        $year = $request->integer('season') ?: null;
        $season = $football->currentSeason($competition, $year);
        abort_if($year && ! $season, 404);

        $this->seo($seo, $competition, ($season ? $season->name.' ' : '').'league table and standings', 'Table');

        return view('football.competitions.table', [
            'competition' => $competition,
            'season' => $season,
            'seasons' => $competition->seasons()->whereHas('standings')->get(),
            'standings' => $season ? $football->standings($season) : collect(),
            'view' => in_array($request->query('view'), ['home', 'away'], true) ? $request->query('view') : 'all',
        ]);
    }

    public function fixtures(string $slug, Seo $seo): View
    {
        $competition = $this->find($slug);
        $this->seo($seo, $competition, 'fixtures and results', 'Fixtures');

        return view('football.competitions.fixtures', ['competition' => $competition]);
    }

    private function find(string $slug): Competition
    {
        $competition = Competition::query()->where('slug', $slug)->where('is_active', true)->with(['country', 'category'])->firstOrFail();

        // The fictional demo league is only reachable while demo data is the active provider.
        abort_if($competition->provider === 'demo' && app(FootballQuery::class)->activeProviderKey() !== 'demo', 404);

        return $competition;
    }

    private function seo(Seo $seo, Competition $competition, string $what, ?string $crumb = null): void
    {
        $seo->set("{$competition->name} {$what}", "{$competition->name} {$what} on SportyCiti.", $crumb ? null : $competition->url());
        $seo->canonical = request()->url().(request()->has('season') ? '?season='.request()->integer('season') : '');
        $seo->noindex = $competition->provider === 'demo';
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))
            ->breadcrumb('Competitions', route('football.competitions.index'))
            ->breadcrumb($competition->name, $crumb ? $competition->url() : null);
        if ($crumb) {
            $seo->breadcrumb($crumb);
        }
    }
}
