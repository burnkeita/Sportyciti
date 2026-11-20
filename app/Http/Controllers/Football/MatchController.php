<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Fixture;
use App\Services\SportsData\FootballQuery;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function show(Fixture $fixture, ?string $slug, FootballQuery $football, Seo $seo): View|RedirectResponse
    {
        abort_unless($fixture->provider === $football->activeProviderKey(), 404);

        $fixture->load(['homeTeam', 'awayTeam', 'competition.country', 'season']);

        if ($slug !== $fixture->slugText()) {
            return redirect($fixture->url(), 301);
        }

        $title = "{$fixture->homeTeam->name} vs {$fixture->awayTeam->name}";
        $seo->set(
            $title.($fixture->isFinished() ? " ({$fixture->home_score}-{$fixture->away_score})" : '').' — '.$fixture->competition->name,
            "{$title}: ".($fixture->isFinished() ? 'result, goals, line-ups and match statistics' : 'kick-off time, line-ups, form and head-to-head').' — '.$fixture->competition->name.'.',
            $fixture->url(),
        );
        $seo->noindex = $fixture->isDemo();
        $seo->breadcrumb('Home', route('home'))
            ->breadcrumb('Football', route('football.index'))
            ->breadcrumb($fixture->competition->name, $fixture->competition->url())
            ->breadcrumb($title);

        $seo->schema(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $title,
            'startDate' => $fixture->kickoff_at->toIso8601String(),
            'eventStatus' => match ($fixture->status) {
                'PST' => 'https://schema.org/EventPostponed',
                'CANC', 'ABD' => 'https://schema.org/EventCancelled',
                default => 'https://schema.org/EventScheduled',
            },
            'location' => $fixture->venue_name ? ['@type' => 'Place', 'name' => $fixture->venue_name, 'address' => $fixture->venue_city] : null,
            'homeTeam' => ['@type' => 'SportsTeam', 'name' => $fixture->homeTeam->name],
            'awayTeam' => ['@type' => 'SportsTeam', 'name' => $fixture->awayTeam->name],
            'sport' => 'Football',
        ]));

        return view('football.match', [
            'fixture' => $fixture,
            'articles' => Article::query()->published()->forCard()->where('fixture_id', $fixture->id)->latest('published_at')->limit(6)->get(),
        ]);
    }
}
