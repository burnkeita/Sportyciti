<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Fixture> */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'kickoff_at' => now()->addDay(),
            'status' => 'NS',
            'provider' => 'demo',
            'external_id' => 'f'.$this->faker->unique()->numberBetween(1, 9999999),
        ];
    }

    public function finished(int $home = 1, int $away = 0): static
    {
        return $this->state(fn () => ['status' => 'FT', 'kickoff_at' => now()->subDay(), 'home_score' => $home, 'away_score' => $away, 'elapsed' => 90]);
    }

    public function live(int $minute = 30): static
    {
        return $this->state(fn () => ['status' => '1H', 'kickoff_at' => now()->subMinutes($minute), 'home_score' => 0, 'away_score' => 0, 'elapsed' => $minute]);
    }
}
