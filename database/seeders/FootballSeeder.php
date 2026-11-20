<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Sport;
use App\Services\SportsData\Providers\ApiFootballProvider;
use App\Services\SportsData\Providers\DemoProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the competitions SportyCiti covers initially.
 *
 * External IDs are API-Football league IDs. IDs verified against the API-Football /leagues endpoint on 2026-11-15. Re-check each season year in
 * Admin → Football → Competitions against your API-Football dashboard before
 * enabling sync; where an ID is not known it is left blank and sync stays off.
 */
class FootballSeeder extends Seeder
{
    public function run(): void
    {
        $football = Sport::updateOrCreate(['slug' => 'football'], ['name' => 'Football', 'is_active' => true]);

        $countries = collect([
            ['Ghana', 'GH'], ['England', 'GB-ENG'], ['Spain', 'ES'], ['Italy', 'IT'], ['Germany', 'DE'], ['France', 'FR'],
            ['Africa', null], ['Europe', null], ['World', null],
        ])->mapWithKeys(fn ($c) => [$c[0] => Country::updateOrCreate(['slug' => Str::slug($c[0])], ['name' => $c[0], 'code' => $c[1]])]);

        $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');

        $competitions = [
            // name, short, country, type, external id, season, standings, category slug, featured
            ['Ghana Premier League', 'GPL', 'Ghana', 'league', '570', 2026, true, 'ghana-premier-league', true],
            ['Ghana FA Cup', 'FA Cup', 'Ghana', 'cup', '966', 2025, false, 'ghana-football-association', false],
            ['Ghana Division One League', 'Division One', 'Ghana', 'league', '1196', 2026, true, 'division-one-league', false],
            ['English Premier League', 'Premier League', 'England', 'league', '39', 2026, true, 'english-premier-league', true],
            ['UEFA Champions League', 'Champions League', 'Europe', 'cup', '2', 2026, true, 'uefa-champions-league', true],
            ['La Liga', 'La Liga', 'Spain', 'league', '140', 2026, true, 'la-liga', true],
            ['Serie A', 'Serie A', 'Italy', 'league', '135', 2026, true, 'serie-a', true],
            ['Bundesliga', 'Bundesliga', 'Germany', 'league', '78', 2026, true, 'bundesliga', true],
            ['Ligue 1', 'Ligue 1', 'France', 'league', '61', 2026, true, 'ligue-1', true],
            ['CAF Champions League', 'CAF CL', 'Africa', 'cup', '12', 2026, true, null, false],
            ['Africa Cup of Nations', 'AFCON', 'Africa', 'cup', '6', 2025, true, 'afcon', false],
            ['FIFA World Cup', 'World Cup', 'World', 'cup', '1', 2026, true, 'fifa-world-cup', false],
        ];

        foreach ($competitions as $i => [$name, $short, $country, $type, $externalId, $season, $standings, $categorySlug, $featured]) {
            Competition::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'sport_id' => $football->id,
                    'country_id' => $countries[$country]->id,
                    'category_id' => $categorySlug ? $cat($categorySlug) : null,
                    'name' => $name,
                    'short_name' => $short,
                    'type' => $type,
                    'provider' => ApiFootballProvider::KEY,
                    'external_id' => $externalId,
                    'current_season_year' => $season,
                    'has_standings' => $standings,
                    'is_active' => true,
                    'is_featured' => $featured,
                    'sync_enabled' => $externalId !== null,
                    'position' => $i,
                ],
            );
        }

        // Fictional league used only by the demonstration provider.
        $demo = Country::updateOrCreate(['slug' => 'demo'], ['name' => 'Demo', 'code' => null]);
        Competition::updateOrCreate(
            ['slug' => 'sportyciti-demo-league'],
            [
                'sport_id' => $football->id,
                'country_id' => $demo->id,
                'name' => 'SportyCiti Demo League',
                'short_name' => 'Demo League',
                'type' => 'league',
                'provider' => DemoProvider::KEY,
                'external_id' => DemoProvider::LEAGUE_ID,
                'current_season_year' => (int) now()->format('Y'),
                'has_standings' => true,
                'is_active' => true,
                'is_featured' => true,
                'sync_enabled' => true,
                'position' => 100,
                'description' => 'Fictional demonstration data used during development. Not a real competition.',
            ],
        );
    }
}
