<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Team> */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = 'Test Team '.$this->faker->unique()->numberBetween(1, 999999);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'provider' => 'demo',
            'external_id' => 't'.$this->faker->unique()->numberBetween(1, 999999),
        ];
    }
}
