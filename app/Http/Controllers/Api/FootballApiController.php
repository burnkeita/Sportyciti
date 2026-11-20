<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Fixture;
use App\Services\SportsData\FootballQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FootballApiController extends Controller
{
    public function __construct(private readonly FootballQuery $football) {}

    public function live(): JsonResponse
    {
        return $this->respond($this->football->baseFixtures()->live()->orderBy('kickoff_at')->get());
    }

    public function fixtures(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'competition' => ['nullable', 'string', 'max:120'],
        ]);

        $date = Carbon::parse($data['date'] ?? 'today', 'UTC');
        $competition = ! empty($data['competition']) ? Competition::where('slug', $data['competition'])->first() : null;

        $fixtures = $this->football->fixtures('all', $date->copy()->startOfDay(), $date->copy()->endOfDay(), ['competition' => $competition?->id]);

        return $this->respond($fixtures);
    }

    public function competitions(): JsonResponse
    {
        return response()->json(['data' => Competition::query()->active()->get()->map(fn ($c) => [
            'name' => $c->name, 'slug' => $c->slug, 'type' => $c->type, 'season' => $c->current_season_year,
        ])]);
    }

    public function standings(string $slug): JsonResponse
    {
        $competition = Competition::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $season = $this->football->currentSeason($competition);

        return response()->json([
            'competition' => $competition->name,
            'season' => $season?->name,
            'demo' => $competition->provider === 'demo',
            'data' => $season ? $this->football->standings($season)->map(fn ($rows) => $rows->map(fn ($s) => [
                'rank' => $s->rank, 'team' => $s->team->name, 'played' => $s->played, 'won' => $s->won, 'drawn' => $s->drawn,
                'lost' => $s->lost, 'goals_for' => $s->goals_for, 'goals_against' => $s->goals_against,
                'goal_difference' => $s->goal_difference, 'points' => $s->points, 'form' => $s->form,
            ])->values()) : [],
        ]);
    }

    private function respond($fixtures): JsonResponse
    {
        return response()->json([
            'demo' => $this->football->isDemo(),
            'data' => $fixtures->map(fn (Fixture $f) => [
                'id' => $f->id,
                'url' => $f->url(),
                'competition' => $f->competition->name,
                'kickoff_at' => $f->kickoff_at->toIso8601String(),
                'status' => $f->status,
                'elapsed' => $f->elapsed,
                'home' => ['name' => $f->homeTeam->name, 'score' => $f->home_score, 'logo' => $f->homeTeam->logo_url],
                'away' => ['name' => $f->awayTeam->name, 'score' => $f->away_score, 'logo' => $f->awayTeam->logo_url],
            ])->values(),
        ]);
    }
}
