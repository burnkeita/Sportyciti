<?php

namespace App\Services\SportsData\Providers;

use App\Services\SportsData\Contracts\FootballDataProvider;
use App\Services\SportsData\Exceptions\ProviderException;
use App\Services\SportsData\Exceptions\RateLimitedException;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Adapter for API-Football v3 (https://www.api-football.com/documentation-v3).
 * Handles authentication, per-minute and daily quota limits, response caching
 * and normalisation into the FootballDataProvider contract shape.
 */
class ApiFootballProvider implements FootballDataProvider
{
    public const KEY = 'api_football';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout = 15,
        private readonly int $requestsPerMinute = 10,
        private readonly int $quotaReserve = 5,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function isDemo(): bool
    {
        return false;
    }

    public function fixturesByDate(CarbonInterface $date): array
    {
        $rows = $this->get('/fixtures', ['date' => $date->format('Y-m-d'), 'timezone' => 'UTC'], config('sportsdata.cache.fixtures'));

        return array_map(fn ($row) => $this->mapFixture($row), $rows);
    }

    public function liveFixtures(): array
    {
        $rows = $this->get('/fixtures', ['live' => 'all', 'timezone' => 'UTC'], config('sportsdata.cache.live'));

        return array_map(fn ($row) => $this->mapFixture($row), $rows);
    }

    public function seasonFixtures(string $leagueId, int $season): array
    {
        $rows = $this->get('/fixtures', ['league' => $leagueId, 'season' => $season, 'timezone' => 'UTC'], config('sportsdata.cache.fixtures'));

        return array_map(fn ($row) => $this->mapFixture($row), $rows);
    }

    public function fixtureDetails(array $fixtureIds): array
    {
        $fixtureIds = array_slice(array_values(array_unique($fixtureIds)), 0, 20);

        if ($fixtureIds === []) {
            return [];
        }

        $rows = $this->get('/fixtures', ['ids' => implode('-', $fixtureIds), 'timezone' => 'UTC'], config('sportsdata.cache.live'));

        return array_map(fn ($row) => $this->mapFixture($row, withDetails: true), $rows);
    }

    public function standings(string $leagueId, int $season): array
    {
        $rows = $this->get('/standings', ['league' => $leagueId, 'season' => $season], config('sportsdata.cache.standings'));
        $groups = $rows[0]['league']['standings'] ?? [];
        $out = [];

        foreach ($groups as $group) {
            foreach ($group as $row) {
                $out[] = [
                    'group' => count($groups) > 1 ? (string) ($row['group'] ?? '') : '',
                    'rank' => (int) $row['rank'],
                    'team' => $this->mapTeam($row['team']),
                    'played' => (int) ($row['all']['played'] ?? 0),
                    'won' => (int) ($row['all']['win'] ?? 0),
                    'drawn' => (int) ($row['all']['draw'] ?? 0),
                    'lost' => (int) ($row['all']['lose'] ?? 0),
                    'goals_for' => (int) ($row['all']['goals']['for'] ?? 0),
                    'goals_against' => (int) ($row['all']['goals']['against'] ?? 0),
                    'goal_difference' => (int) ($row['goalsDiff'] ?? 0),
                    'points' => (int) ($row['points'] ?? 0),
                    'form' => $row['form'] ?? null,
                    'description' => $row['description'] ?? null,
                    'home' => $this->mapSplit($row['home'] ?? null),
                    'away' => $this->mapSplit($row['away'] ?? null),
                ];
            }
        }

        return $out;
    }

    public function headToHead(string $homeTeamId, string $awayTeamId, int $last = 10): array
    {
        $rows = $this->get('/fixtures/headtohead', ['h2h' => "{$homeTeamId}-{$awayTeamId}", 'last' => $last, 'timezone' => 'UTC'], config('sportsdata.cache.head_to_head'));

        return array_map(fn ($row) => $this->mapFixture($row), $rows);
    }

    // ---------------------------------------------------------------------
    // HTTP
    // ---------------------------------------------------------------------

    /** @return list<array> */
    private function get(string $path, array $query, int $ttl): array
    {
        ksort($query);
        $cacheKey = 'apifootball:'.md5($path.'?'.http_build_query($query));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $this->guardQuota();

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->withHeaders(['x-apisports-key' => $this->apiKey, 'Accept' => 'application/json'])
                ->timeout($this->timeout)
                ->retry(2, 750, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->get($path, $query);
        } catch (ConnectionException $e) {
            throw new ProviderException('API-Football connection failed: '.$e->getMessage(), previous: $e);
        }

        $this->recordQuota($response);

        if ($response->status() === 429) {
            throw new RateLimitedException('API-Football returned HTTP 429 (too many requests).', 60);
        }

        if ($response->failed()) {
            throw new ProviderException("API-Football HTTP {$response->status()} for {$path}");
        }

        $errors = $response->json('errors');
        if (! empty($errors)) {
            $message = is_array($errors) ? implode('; ', array_map(fn ($k, $v) => is_string($k) ? "{$k}: {$v}" : (string) $v, array_keys($errors), $errors)) : (string) $errors;

            if (str_contains(strtolower($message), 'ratelimit') || str_contains(strtolower($message), 'too many')) {
                throw new RateLimitedException('API-Football: '.$message);
            }

            throw new ProviderException('API-Football: '.$message);
        }

        $data = $response->json('response') ?? [];
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    private function guardQuota(): void
    {
        $remaining = Cache::get('apifootball:daily_remaining');
        if ($remaining !== null && (int) $remaining <= $this->quotaReserve) {
            throw new RateLimitedException('API-Football daily request quota is nearly exhausted; sync paused until the quota resets.', 3600);
        }

        $allowed = RateLimiter::attempt('apifootball:minute', $this->requestsPerMinute, fn () => true, 60);
        if (! $allowed) {
            throw new RateLimitedException('Per-minute request budget for API-Football used; will retry shortly.', RateLimiter::availableIn('apifootball:minute'));
        }
    }

    private function recordQuota(Response $response): void
    {
        $remaining = $response->header('x-ratelimit-requests-remaining');
        $limit = $response->header('x-ratelimit-requests-limit');

        if ($remaining !== '' && $remaining !== null) {
            Cache::put('apifootball:daily_remaining', (int) $remaining, now()->endOfDay());
        }
        if ($limit !== '' && $limit !== null) {
            Cache::put('apifootball:daily_limit', (int) $limit, now()->endOfDay());
        }
    }

    // ---------------------------------------------------------------------
    // Normalisation
    // ---------------------------------------------------------------------

    private function mapFixture(array $row, bool $withDetails = false): array
    {
        $fixture = $row['fixture'] ?? [];
        $league = $row['league'] ?? [];
        $score = $row['score'] ?? [];

        $mapped = [
            'external_id' => (string) $fixture['id'],
            'league' => [
                'external_id' => isset($league['id']) ? (string) $league['id'] : null,
                'name' => $league['name'] ?? null,
                'country' => $league['country'] ?? null,
                'logo' => $league['logo'] ?? null,
                'season' => isset($league['season']) ? (int) $league['season'] : null,
                'round' => $league['round'] ?? null,
            ],
            'kickoff_at' => isset($fixture['timestamp']) ? Carbon::createFromTimestampUTC($fixture['timestamp'])->toIso8601String() : ($fixture['date'] ?? null),
            'status' => $fixture['status']['short'] ?? 'NS',
            'status_long' => $fixture['status']['long'] ?? null,
            'elapsed' => $fixture['status']['elapsed'] ?? null,
            'venue_name' => $fixture['venue']['name'] ?? null,
            'venue_city' => $fixture['venue']['city'] ?? null,
            'referee' => $fixture['referee'] ?? null,
            'home' => $this->mapTeam($row['teams']['home'] ?? []),
            'away' => $this->mapTeam($row['teams']['away'] ?? []),
            'score' => [
                'home' => $row['goals']['home'] ?? null,
                'away' => $row['goals']['away'] ?? null,
                'ht_home' => $score['halftime']['home'] ?? null,
                'ht_away' => $score['halftime']['away'] ?? null,
                'pen_home' => $score['penalty']['home'] ?? null,
                'pen_away' => $score['penalty']['away'] ?? null,
            ],
            'events' => null,
            'lineups' => null,
            'statistics' => null,
        ];

        if ($withDetails || array_key_exists('events', $row)) {
            $mapped['events'] = array_map(fn ($e) => [
                'team_external_id' => isset($e['team']['id']) ? (string) $e['team']['id'] : null,
                'minute' => (int) ($e['time']['elapsed'] ?? 0),
                'extra_minute' => $e['time']['extra'] ?? null,
                'type' => match (strtolower((string) ($e['type'] ?? ''))) {
                    'goal' => 'goal',
                    'card' => 'card',
                    'subst' => 'subst',
                    'var' => 'var',
                    default => 'other',
                },
                'detail' => $e['detail'] ?? null,
                'player' => $e['player']['name'] ?? null,
                'assist' => $e['assist']['name'] ?? null,
                'comments' => $e['comments'] ?? null,
            ], $row['events'] ?? []);
        }

        if ($withDetails || array_key_exists('lineups', $row)) {
            $mapPlayer = fn ($p) => [
                'name' => $p['player']['name'] ?? null,
                'number' => $p['player']['number'] ?? null,
                'position' => $p['player']['pos'] ?? null,
                'grid' => $p['player']['grid'] ?? null,
            ];

            $mapped['lineups'] = array_map(fn ($l) => [
                'team_external_id' => (string) ($l['team']['id'] ?? ''),
                'formation' => $l['formation'] ?? null,
                'coach' => $l['coach']['name'] ?? null,
                'starting_xi' => array_map($mapPlayer, $l['startXI'] ?? []),
                'substitutes' => array_map($mapPlayer, $l['substitutes'] ?? []),
            ], $row['lineups'] ?? []);
        }

        if ($withDetails || array_key_exists('statistics', $row)) {
            $mapped['statistics'] = array_map(fn ($s) => [
                'team_external_id' => (string) ($s['team']['id'] ?? ''),
                'statistics' => array_map(fn ($stat) => ['type' => (string) $stat['type'], 'value' => $stat['value'] === null ? null : (string) $stat['value']], $s['statistics'] ?? []),
            ], $row['statistics'] ?? []);
        }

        return $mapped;
    }

    private function mapTeam(array $team): array
    {
        return [
            'external_id' => (string) ($team['id'] ?? ''),
            'name' => (string) ($team['name'] ?? 'Unknown'),
            'logo' => $team['logo'] ?? null,
        ];
    }

    private function mapSplit(?array $split): ?array
    {
        if (! $split) {
            return null;
        }

        return [
            'played' => (int) ($split['played'] ?? 0),
            'won' => (int) ($split['win'] ?? 0),
            'drawn' => (int) ($split['draw'] ?? 0),
            'lost' => (int) ($split['lose'] ?? 0),
            'goals_for' => (int) ($split['goals']['for'] ?? 0),
            'goals_against' => (int) ($split['goals']['against'] ?? 0),
        ];
    }
}
