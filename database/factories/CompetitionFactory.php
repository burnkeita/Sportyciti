<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Competition> */
class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        $name = 'Test League '.$this->faker->unique()->numberBetween(1, 99999);

        return [
            'sport_id' => fn () => Sport::firstOrCreate(['slug' => 'football'], ['name' => 'Football'])->id,
            'name' => $name,
            'slug' => str($name)->slug(),
            'type' => 'league',
            'provider' => 'demo',
            'external_id' => (string) $this->faker->unique()->numberBetween(1000, 999999),
            'current_season_year' => 2026,
            'is_active' => true,
            'sync_enabled' => true,
            'has_standings' => true,
        ];
    }
}
