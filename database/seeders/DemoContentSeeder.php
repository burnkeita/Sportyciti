<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;

/**
 * LOCAL DEVELOPMENT ONLY — placeholder editorial content so the layout can be reviewed.
 * Every article is explicitly marked as a sample and contains no factual claims,
 * statistics or quotes. Remove with: php artisan tinker → Article::where('slug','like','sample-%')->forceDelete()
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::where('slug', 'like', 'sample-%')->exists()) {
            return;
        }

        $authors = collect([
            ['Sample Editor', Permissions::EDITOR, 'Editor', 'Ghanaian football'],
            ['Sample Journalist', Permissions::JOURNALIST, 'Sports Journalist', 'International football'],
            ['Sample Analyst', Permissions::JOURNALIST, 'Football Analyst', 'Tactics and data'],
        ])->map(function ($a, $i) {
            $user = User::firstOrCreate(['email' => 'sample'.($i + 1).'@sportyciti.test'], [
                'name' => $a[0], 'password' => str()->password(20), 'job_title' => $a[2], 'specialisation' => $a[3],
                'bio' => 'Placeholder author profile for local development.', 'is_public_author' => true,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles([$a[1]]);

            return $user;
        });

        $tags = collect(['Black Stars', 'Transfers', 'Match preview', 'Tactics', 'Ghana Premier League', 'AFCON'])
            ->map(fn ($name) => Tag::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]));

        $paragraph = '<p>This is placeholder copy used to preview the SportyCiti article layout during development. It contains no real reporting, quotes or statistics and must be replaced with verified editorial content before launch.</p>';

        $children = Category::whereNotNull('parent_id')->with('parent')->get();
        $n = 0;

        foreach ($children as $category) {
            foreach (range(1, 2) as $i) {
                $n++;
                $isInsight = $category->parent?->slug === 'insights';

                $article = Article::create([
                    'title' => "Sample story {$n}: {$category->name} coverage placeholder",
                    'slug' => "sample-story-{$n}",
                    'excerpt' => "Placeholder summary for the {$category->name} section. Replace with real SportyCiti journalism.",
                    'body' => str_repeat($paragraph, 6),
                    'type' => $isInsight ? ArticleType::Insight : ArticleType::News,
                    'insight_type' => $isInsight ? InsightType::cases()[$n % count(InsightType::cases())] : null,
                    'insight_blocks' => $isInsight ? [
                        ['type' => 'key_takeaways', 'data' => ['heading' => 'Key takeaways', 'items' => [['text' => 'Placeholder takeaway one.'], ['text' => 'Placeholder takeaway two.']]]],
                    ] : null,
                    'status' => ArticleStatus::Published,
                    'category_id' => $category->parent_id,
                    'subcategory_id' => $category->id,
                    'author_id' => $authors[$n % 3]->id,
                    'published_at' => now()->subHours($n * 3),
                    'is_featured' => $n <= 5,
                    'is_breaking' => $n === 1,
                ]);
                $article->forceFill(['view_count' => ($n * 37) % 500])->saveQuietly();
                $article->tags()->sync($tags->random(2)->pluck('id'));
            }
        }
    }
}
