<?php

namespace App\Services\SportsData\Contracts;

use Carbon\CarbonInterface;

/**
 * Contract every football data provider adapter must satisfy.
 *
 * All methods return provider-agnostic, normalised arrays:
 *
 * Fixture:  [
 *   'external_id' => string, 'league' => ['external_id', 'name', 'country', 'logo', 'season', 'round'],
 *   'kickoff_at' => ISO-8601 string (UTC), 'status' => short code, 'status_long' => string, 'elapsed' => ?int,
 *   'venue_name' => ?string, 'venue_city' => ?string, 'referee' => ?string,
 *   'home' => Team, 'away' => Team,
 *   'score' => ['home' => ?int, 'away' => ?int, 'ht_home' => ?int, 'ht_away' => ?int, 'pen_home' => ?int, 'pen_away' => ?int],
 *   'events' => ?list<Event>, 'lineups' => ?list<Lineup>, 'statistics' => ?list<TeamStats>,
 * ]
 * Team:     ['external_id' => string, 'name' => string, 'logo' => ?string]
 * Event:    ['team_external_id', 'minute', 'extra_minute', 'type' (goal|card|subst|var), 'detail', 'player', 'assist', 'comments']
 * Lineup:   ['team_external_id', 'formation', 'coach', 'starting_xi' => list<Player>, 'substitutes' => list<Player>]
 * Player:   ['name', 'number', 'position', 'grid']
 * TeamStats:['team_external_id', 'statistics' => list<['type', 'value']>]
 * Standing: ['group', 'rank', 'team' => Team, 'played', 'won', 'drawn', 'lost', 'goals_for', 'goals_against',
 *            'goal_difference', 'points', 'form', 'description', 'home' => ?array, 'away' => ?array]
 */
interface FootballDataProvider
{
    /** Identifier stored in the `provider` column, e.g. "api_football" or "demo". */
    public function key(): string;

    /** True when the provider serves fictional demonstration data. */
    public function isDemo(): bool;

    /** @return list<array> fixtures on the given UTC date, across all leagues the provider covers */
    public function fixturesByDate(CarbonInterface $date): array;

    /** @return list<array> fixtures currently in play */
    public function liveFixtures(): array;

    /** @return list<array> every fixture of a league season */
    public function seasonFixtures(string $leagueId, int $season): array;

    /**
     * Full fixture details (events, lineups, statistics) for up to 20 fixtures.
     *
     * @param  list<string>  $fixtureIds
     * @return list<array>
     */
    public function fixtureDetails(array $fixtureIds): array;

    /** @return list<array> standings rows */
    public function standings(string $leagueId, int $season): array;

    /** @return list<array> recent meetings between the two teams, newest first */
    public function headToHead(string $homeTeamId, string $awayTeamId, int $last = 10): array;
}
