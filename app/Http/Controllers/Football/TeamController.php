<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Standing;
use App\Models\Team;
use App\Models\Transfer;
use App\Services\SportsData\FootballQuery;
use App\Support\Seo;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function show(string $slug, FootballQuery $football, Seo $seo): View
    {
        $team = Team::query()->where('slug', $slug)->with(['country', 'competitions' => fn ($q) => $q->where('is_active', true)])->firstOrFail();
        abort_unless($team->provider === $football->activeProviderKey() || $team->provider === null, 404);

        $seo->set("{$team->name} fixtures, results, form and transfers", "{$team->name}: upcoming fixtures, latest results, recent form, league position and transfer news.", $team->url(), $team->logo_url);
        $seo->noindex = $team->provider === 'demo';
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb($team->name);
        $seo->schema(array_filter(['@context' => 'https://schema.org', '@type' => 'SportsTeam', 'name' => $team->name, 'sport' => 'Football', 'logo' => $team->logo_url]));

        $positions = Standing::query()->where('team_id', $team->id)
            ->whereHas('season', fn ($q) => $q->where('is_current', true))
            ->with('season.competition')->get();

        return view('football.team', [
            'team' => $team,
            'upcoming' => $football->teamFixtures($team, results: false, limit: 8),
            'results' => $football->teamFixtures($team, results: true, limit: 8),
            'form' => $football->recentForm($team, 5),
            'positions' => $positions,
            'transfers' => Transfer::query()->public()->forTeam($team->id)->with(['fromTeam', 'toTeam', 'player'])->latest('reported_at')->limit(10)->get(),
            'news' => Article::query()->published()->forCard()
                ->whereHas('tags', fn ($q) => $q->where('name', $team->name))
                ->latest('published_at')->limit(6)->get(),
        ]);
    }
}
