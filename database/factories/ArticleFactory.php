<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Article> */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => rtrim($this->faker->unique()->sentence(6), '.'),
            'excerpt' => $this->faker->sentence(18),
            'body' => '<p>'.implode('</p><p>', $this->faker->paragraphs(4)).'</p>',
            'type' => ArticleType::News,
            'status' => ArticleStatus::Draft,
            'category_id' => Category::factory(),
            'author_id' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ArticleStatus::Published, 'published_at' => now()->subHour()]);
    }

    public function inReview(): static
    {
        return $this->state(fn () => ['status' => ArticleStatus::InReview]);
    }
}
