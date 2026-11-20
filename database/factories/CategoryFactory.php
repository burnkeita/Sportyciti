<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'is_active' => true,
            'show_in_nav' => true,
            'position' => 0,
        ];
    }
}
