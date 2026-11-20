<?php

namespace App\Services\SportsData\Providers;

use App\Services\SportsData\Contracts\FootballDataProvider;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * DEMONSTRATION DATA ONLY.
 *
 * Generates deterministic, entirely fictional fixtures for a fictional
 * "SportyCiti Demo League" so the live-score, fixtures and table interfaces can be
 * developed and tested without a licensed provider. No real team, player,
 * competition or result is represented. Every row it produces is stored with
 * provider = "demo" and is labelled as demonstration data in the UI.
 */
class DemoProvider implements FootballDataProvider
{
    public const KEY = 'demo';

    public const LEAGUE_ID = 'demo-league';

    private const TEAMS = [
        'demo-1' => 'Demo Rovers',
        'demo-2' => 'Demo City',
        'demo-3' => 'Demo Athletic',
        'demo-4' => 'Demo United',
        'demo-5' => 'Demo Wanderers',
        'demo-6' => 'Demo Albion',
        'demo-7' => 'Demo Rangers',
        'demo-8' => 'Demo Harbour',
    ];

    private const KICKOFF_HOURS = [12, 15, 18, 20];

    public function key(): string
    {
        return self::KEY;
    }

    public function isDemo(): bool
    {
        return true;
    }

    public function fixturesByDate(CarbonInterface $date): array
    {
        $day = Carbon::parse($date->format('Y-m-d'), 'UTC')->startOfDay();
        $round = intdiv($day->timestamp, 86400) % 7;
        $pairs = $this->roundRobinPairs($round);
        $out = [];

        foreach ($pairs as $i => [$home, $away]) {
            $out[] = $this->buildFixture($day->copy()->setHour(self::KICKOFF_HOURS[$i]), $home, $away, $i);
        }

        return $out;
    }

    public function liveFixtures(): array
    {
        return array_values(array_filter(
            $this->fixturesByDate(now('UTC')),
            fn ($f) => in_array($f['status'], ['1H', 'HT', '2H'], true),
        ));
    }

    public function seasonFixtures(string $leagueId, int $season): array
    {
        $out = [];
        for ($d = -21; $d <= 21; $d++) {
            array_push($out, ...$this->fixturesByDate(now('UTC')->addDays($d)));
        }

        return $out;
    }

    public function fixtureDetails(array $fixtureIds): array
    {
        $out = [];
        foreach ($fixtureIds as $id) {
            // demo-YYYYMMDD-n
            if (! preg_match('/^demo-(\d{8})-(\d)$/', (string) $id, $m)) {
                continue;
            }
            $fixtures = $this->fixturesByDate(Carbon::createFromFormat('Ymd', $m[1], 'UTC'));
            if (isset($fixtures[(int) $m[2]])) {
                $out[] = $this->withDetails($fixtures[(int) $m[2]]);
            }
        }

        return $out;
    }

    public function standings(string $leagueId, int $season): array
    {
        $table = [];
        foreach (self::TEAMS as $id => $name) {
            $table[$id] = ['team' => $this->team($id), 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'points' => 0, 'form' => ''];
        }

        foreach ($this->seasonFixtures($leagueId, $season) as $f) {
            if ($f['status'] !== 'FT') {
                continue;
            }
            [$h, $a] = [$f['home']['external_id'], $f['away']['external_id']];
            [$hs, $as] = [$f['score']['home'], $f['score']['away']];

            foreach ([[$h, $hs, $as], [$a, $as, $hs]] as [$team, $for, $against]) {
                $table[$team]['played']++;
                $table[$team]['goals_for'] += $for;
                $table[$team]['goals_against'] += $against;
                $result = $for > $against ? 'W' : ($for < $against ? 'L' : 'D');
                $table[$team][['W' => 'won', 'D' => 'drawn', 'L' => 'lost'][$result]]++;
                $table[$team]['points'] += ['W' => 3, 'D' => 1, 'L' => 0][$result];
                $table[$team]['form'] = substr($result.$table[$team]['form'], 0, 5);
            }
        }

        $rows = array_values($table);
        usort($rows, fn ($x, $y) => [$y['points'], $y['goals_for'] - $y['goals_against'], $y['goals_for']] <=> [$x['points'], $x['goals_for'] - $x['goals_against'], $x['goals_for']]);

        return array_map(fn ($row, $i) => $row + [
            'group' => '',
            'rank' => $i + 1,
            'goal_difference' => $row['goals_for'] - $row['goals_against'],
            'description' => null,
            'home' => null,
            'away' => null,
        ], $rows, array_keys($rows));
    }

    public function headToHead(string $homeTeamId, string $awayTeamId, int $last = 10): array
    {
        $out = [];
        for ($d = 0; $d >= -90 && count($out) < $last; $d--) {
            foreach ($this->fixturesByDate(now('UTC')->addDays($d)) as $f) {
                $pair = [$f['home']['external_id'], $f['away']['external_id']];
                if ($f['status'] === 'FT' && in_array($homeTeamId, $pair, true) && in_array($awayTeamId, $pair, true)) {
                    $out[] = $f;
                }
            }
        }

        return array_slice($out, 0, $last);
    }

    // ---------------------------------------------------------------------

    /** Circle-method round robin for 8 teams: 7 rounds of 4 pairings. */
    private function roundRobinPairs(int $round): array
    {
        $ids = array_keys(self::TEAMS);
        $fixed = array_shift($ids);
        $rotated = array_merge(array_slice($ids, $round % 7), array_slice($ids, 0, $round % 7));
        $list = array_merge([$fixed], $rotated);
        $pairs = [];
        for ($i = 0; $i < 4; $i++) {
            $a = $list[$i];
            $b = $list[7 - $i];
            $pairs[] = ($round + $i) % 2 === 0 ? [$a, $b] : [$b, $a];
        }

        return $pairs;
    }

    private function buildFixture(Carbon $kickoff, string $home, string $away, int $index): array
    {
        $externalId = 'demo-'.$kickoff->format('Ymd').'-'.$index;
        $minutesSince = (int) floor((now('UTC')->timestamp - $kickoff->timestamp) / 60);

        [$status, $elapsed] = match (true) {
            $minutesSince < 0 => ['NS', null],
            $minutesSince < 47 => ['1H', max(1, $minutesSince)],
            $minutesSince < 62 => ['HT', 45],
            $minutesSince < 109 => ['2H', min(90, $minutesSince - 17)],
            default => ['FT', 90],
        };

        $goals = $this->goalsFor($externalId, $home, $away);
        $upTo = $status === 'NS' ? -1 : ($status === 'FT' ? 999 : $elapsed);
        $scored = array_filter($goals, fn ($g) => $g['minute'] <= $upTo);
        $hs = count(array_filter($scored, fn ($g) => $g['team'] === $home));
        $as = count(array_filter($scored, fn ($g) => $g['team'] === $away));
        $htGoals = array_filter($goals, fn ($g) => $g['minute'] <= 45);

        return [
            'external_id' => $externalId,
            'league' => ['external_id' => self::LEAGUE_ID, 'name' => 'SportyCiti Demo League', 'country' => 'Demo', 'logo' => null, 'season' => (int) now('UTC')->format('Y'), 'round' => 'Demo round'],
            'kickoff_at' => $kickoff->toIso8601String(),
            'status' => $status,
            'status_long' => null,
            'elapsed' => $elapsed,
            'venue_name' => 'Demo Stadium',
            'venue_city' => null,
            'referee' => null,
            'home' => $this->team($home),
            'away' => $this->team($away),
            'score' => [
                'home' => $status === 'NS' ? null : $hs,
                'away' => $status === 'NS' ? null : $as,
                'ht_home' => in_array($status, ['HT', '2H', 'FT'], true) ? count(array_filter($htGoals, fn ($g) => $g['team'] === $home)) : null,
                'ht_away' => in_array($status, ['HT', '2H', 'FT'], true) ? count(array_filter($htGoals, fn ($g) => $g['team'] === $away)) : null,
                'pen_home' => null,
                'pen_away' => null,
            ],
            'events' => null,
            'lineups' => null,
            'statistics' => null,
            '_goals' => array_values($scored),
        ];
    }

    private function withDetails(array $fixture): array
    {
        $home = $fixture['home']['external_id'];
        $away = $fixture['away']['external_id'];
        $events = array_map(fn ($g) => [
            'team_external_id' => $g['team'],
            'minute' => $g['minute'],
            'extra_minute' => null,
            'type' => 'goal',
            'detail' => 'Normal Goal',
            'player' => self::TEAMS[$g['team']].' Player '.$g['shirt'],
            'assist' => null,
            'comments' => null,
        ], $fixture['_goals']);

        $hasStarted = $fixture['status'] !== 'NS';
        $seed = crc32($fixture['external_id']);

        $fixture['events'] = $events;
        $fixture['lineups'] = array_map(fn ($team) => [
            'team_external_id' => $team,
            'formation' => ['4-3-3', '4-4-2', '4-2-3-1', '3-5-2'][crc32($team) % 4],
            'coach' => self::TEAMS[$team].' Coach',
            'starting_xi' => array_map(fn ($n) => ['name' => self::TEAMS[$team].' Player '.$n, 'number' => $n, 'position' => $n === 1 ? 'G' : ($n <= 5 ? 'D' : ($n <= 8 ? 'M' : 'F')), 'grid' => null], range(1, 11)),
            'substitutes' => array_map(fn ($n) => ['name' => self::TEAMS[$team].' Player '.$n, 'number' => $n, 'position' => null, 'grid' => null], range(12, 18)),
        ], [$home, $away]);
        $possession = 40 + $seed % 21;
        $fixture['statistics'] = $hasStarted ? [
            ['team_external_id' => $home, 'statistics' => [['type' => 'Ball Possession', 'value' => $possession.'%'], ['type' => 'Total Shots', 'value' => (string) (5 + $seed % 9)], ['type' => 'Shots on Goal', 'value' => (string) (1 + $seed % 6)], ['type' => 'Corner Kicks', 'value' => (string) ($seed % 8)]]],
            ['team_external_id' => $away, 'statistics' => [['type' => 'Ball Possession', 'value' => (100 - $possession).'%'], ['type' => 'Total Shots', 'value' => (string) (4 + intdiv($seed, 7) % 9)], ['type' => 'Shots on Goal', 'value' => (string) (1 + intdiv($seed, 7) % 6)], ['type' => 'Corner Kicks', 'value' => (string) (intdiv($seed, 5) % 8)]]],
        ] : [];

        return $fixture;
    }

    /** Deterministic goal list for a fixture. */
    private function goalsFor(string $fixtureId, string $home, string $away): array
    {
        mt_srand(crc32($fixtureId));
        $count = mt_rand(0, 5);
        $goals = [];
        for ($i = 0; $i < $count; $i++) {
            $goals[] = ['minute' => mt_rand(3, 90), 'team' => mt_rand(0, 9) < 5 ? $home : $away, 'shirt' => mt_rand(7, 11)];
        }
        mt_srand();
        usort($goals, fn ($a, $b) => $a['minute'] <=> $b['minute']);

        return $goals;
    }

    private function team(string $id): array
    {
        return ['external_id' => $id, 'name' => self::TEAMS[$id], 'logo' => null];
    }
}
