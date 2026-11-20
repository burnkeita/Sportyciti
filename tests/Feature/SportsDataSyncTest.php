<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\Fixture;
use App\Models\Standing;
use App\Models\SyncLog;
use App\Services\SportsData\FootballSyncService;
use App\Services\SportsData\ProviderResolver;
use App\Services\SportsData\Providers\ApiFootballProvider;
use App\Services\SportsData\Providers\DemoProvider;
use App\Services\SportsData\SyncRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SportsDataSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    private function apiProvider(): ApiFootballProvider
    {
        return new ApiFootballProvider('test-key', 'https://v3.football.api-sports.io', requestsPerMinute: 100);
    }

    private function apiFixture(int $id, int $leagueId, string $status = 'FT', array $extra = []): array
    {
        return array_merge([
            'fixture' => ['id' => $id, 'referee' => 'Referee Name', 'timestamp' => now()->subHours(3)->timestamp, 'venue' => ['name' => 'Accra Sports Stadium', 'city' => 'Accra'], 'status' => ['short' => $status, 'long' => 'Match Finished', 'elapsed' => 90]],
            'league' => ['id' => $leagueId, 'name' => 'Premier League', 'country' => 'Ghana', 'logo' => null, 'season' => 2026, 'round' => 'Regular Season - 10'],
            'teams' => ['home' => ['id' => 101, 'name' => 'Home FC', 'logo' => 'https://media.api-sports.io/football/teams/101.png'], 'away' => ['id' => 202, 'name' => 'Away FC', 'logo' => null]],
            'goals' => ['home' => 2, 'away' => 1],
            'score' => ['halftime' => ['home' => 1, 'away' => 0], 'penalty' => ['home' => null, 'away' => null]],
        ], $extra);
    }

    public function test_api_football_fixtures_are_normalised_and_persisted_for_configured_competitions_only(): void
    {
        Http::fake([
            'v3.football.api-sports.io/fixtures*' => Http::response([
                'errors' => [],
                'response' => [$this->apiFixture(9001, 570), $this->apiFixture(9002, 99999)],
            ], 200, ['x-ratelimit-requests-remaining' => '95', 'x-ratelimit-requests-limit' => '100']),
        ]);

        $count = (new FootballSyncService($this->apiProvider()))->syncDate(now());

        $this->assertSame(1, $count, 'Only the Ghana Premier League (570) is configured.');
        $fixture = Fixture::where('external_id', '9001')->firstOrFail();
        $this->assertSame('FT', $fixture->status);
        $this->assertSame(2, $fixture->home_score);
        $this->assertSame(1, $fixture->home_ht_score);
        $this->assertSame('Home FC', $fixture->homeTeam->name);
        $this->assertSame('Accra Sports Stadium', $fixture->venue_name);
        $this->assertSame('ghana-premier-league', $fixture->competition->slug);
        $this->assertDatabaseMissing('fixtures', ['external_id' => '9002']);

        Http::assertSent(fn ($request) => $request->hasHeader('x-apisports-key', 'test-key'));
    }

    public function test_fixture_details_events_lineups_and_statistics_are_stored(): void
    {
        Http::fake([
            'v3.football.api-sports.io/fixtures*' => Http::response(['errors' => [], 'response' => [$this->apiFixture(9001, 570, '2H', [
                'events' => [
                    ['time' => ['elapsed' => 23, 'extra' => null], 'team' => ['id' => 101], 'player' => ['name' => 'Scorer One'], 'assist' => ['name' => 'Assist One'], 'type' => 'Goal', 'detail' => 'Normal Goal', 'comments' => null],
                    ['time' => ['elapsed' => 45, 'extra' => 2], 'team' => ['id' => 202], 'player' => ['name' => 'Booked Two'], 'assist' => ['name' => null], 'type' => 'Card', 'detail' => 'Yellow Card', 'comments' => null],
                ],
                'lineups' => [['team' => ['id' => 101], 'formation' => '4-3-3', 'coach' => ['name' => 'Coach A'], 'startXI' => [['player' => ['name' => 'Keeper', 'number' => 1, 'pos' => 'G', 'grid' => '1:1']]], 'substitutes' => []]],
                'statistics' => [['team' => ['id' => 101], 'statistics' => [['type' => 'Ball Possession', 'value' => '58%']]]],
            ])]]),
        ]);

        $service = new FootballSyncService($this->apiProvider());
        $service->persistFixtures($this->apiProvider()->fixtureDetails(['9001']), withDetails: true);

        $fixture = Fixture::where('external_id', '9001')->firstOrFail();
        $this->assertTrue($fixture->isLive());
        $this->assertCount(2, $fixture->events);
        $this->assertSame('goal', $fixture->events[0]->type);
        $this->assertSame("45+2'", $fixture->events[1]->minuteLabel());
        $this->assertSame('4-3-3', $fixture->lineups->first()->formation);
        $this->assertSame('58%', $fixture->statistics->first()->value);
        $this->assertNotNull($fixture->details_synced_at);
    }

    public function test_standings_are_synced(): void
    {
        Http::fake([
            'v3.football.api-sports.io/standings*' => Http::response(['errors' => [], 'response' => [['league' => ['standings' => [[
                ['rank' => 1, 'team' => ['id' => 101, 'name' => 'Home FC', 'logo' => null], 'points' => 22, 'goalsDiff' => 9, 'group' => 'Premier League', 'form' => 'WWDWL', 'description' => 'CAF Champions League',
                    'all' => ['played' => 10, 'win' => 7, 'draw' => 1, 'lose' => 2, 'goals' => ['for' => 18, 'against' => 9]],
                    'home' => ['played' => 5, 'win' => 4, 'draw' => 1, 'lose' => 0, 'goals' => ['for' => 10, 'against' => 3]],
                    'away' => ['played' => 5, 'win' => 3, 'draw' => 0, 'lose' => 2, 'goals' => ['for' => 8, 'against' => 6]]],
            ]]]]]]),
        ]);

        $competition = Competition::where('slug', 'ghana-premier-league')->firstOrFail();
        $rows = (new FootballSyncService($this->apiProvider()))->syncStandings($competition);

        $this->assertSame(1, $rows);
        $standing = Standing::firstOrFail();
        $this->assertSame(22, $standing->points);
        $this->assertSame(7, $standing->won);
        $this->assertSame(4, $standing->home['won']);
        $this->assertTrue($standing->season->is_current);
    }

    public function test_provider_errors_are_logged_and_open_the_circuit_breaker(): void
    {
        config(['sportsdata.circuit_failures' => 2]);
        Http::fake(['*' => Http::response(['errors' => ['token' => 'Error/Missing application key.'], 'response' => []])]);

        $runner = new SyncRunner(new FootballSyncService($this->apiProvider()));

        $first = $runner->run('fixtures:date', fn ($s) => $s->syncDate(now()));
        $runner->run('fixtures:date', fn ($s) => $s->syncDate(now()->addDay()));
        $third = $runner->run('fixtures:date', fn ($s) => $s->syncDate(now()->addDays(2)));

        $this->assertSame('failed', $first->status);
        $this->assertStringContainsString('Missing application key', $first->message);
        $this->assertSame('skipped', $third->status, 'Circuit should be open after repeated failures.');
    }

    public function test_rate_limit_is_respected_without_failing(): void
    {
        Http::fake(['*' => Http::response(['errors' => [], 'response' => []])]);
        $provider = new ApiFootballProvider('k', 'https://v3.football.api-sports.io', requestsPerMinute: 1);
        $runner = new SyncRunner(new FootballSyncService($provider));

        $this->assertSame('success', $runner->run('fixtures:date', fn ($s) => $s->syncDate(now()))->status);
        $this->assertSame('skipped', $runner->run('fixtures:date', fn ($s) => $s->syncDate(now()->addDay()))->status);
        Http::assertSentCount(1);
    }

    public function test_demo_provider_produces_clearly_labelled_fictional_data(): void
    {
        $service = new FootballSyncService(new DemoProvider);
        $competition = Competition::where('provider', 'demo')->firstOrFail();

        $this->assertGreaterThan(0, $service->syncSeason($competition));
        $this->assertGreaterThan(0, $service->syncStandings($competition));

        $this->assertSame(0, Fixture::where('provider', '!=', 'demo')->count());
        Fixture::with('homeTeam')->each(fn ($f) => $this->assertStringStartsWith('Demo ', $f->homeTeam->name));
    }

    public function test_resolver_falls_back_to_demo_only_when_enabled(): void
    {
        config(['sportsdata.provider' => 'api_football', 'sportsdata.api_football.key' => null]);

        config(['sportsdata.demo_fallback' => true]);
        $this->assertTrue(app(ProviderResolver::class)->resolve()->isDemo());

        config(['sportsdata.demo_fallback' => false]);
        $this->assertFalse(app(ProviderResolver::class)->isConfigured());

        config(['sportsdata.api_football.key' => 'abc']);
        $this->assertFalse(app(ProviderResolver::class)->resolve()->isDemo());
    }

    public function test_sync_command_runs(): void
    {
        config(['sportsdata.provider' => 'demo']);

        $this->artisan('football:sync', ['resource' => 'dates'])->assertSuccessful();

        $this->assertGreaterThan(0, Fixture::count());
        $this->assertTrue(SyncLog::where('status', 'success')->exists());
    }
}
