<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Services\Content\ArticleWorkflow;
use App\Support\Permissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ArticleWorkflow $workflow;

    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->workflow = app(ArticleWorkflow::class);
        $this->categoryId = Category::whereNull('parent_id')->value('id');
    }

    public function test_contributor_cannot_publish_through_the_workflow(): void
    {
        $contributor = $this->userWithRole(Permissions::CONTRIBUTOR);

        $this->expectException(ValidationException::class);
        $this->workflow->normalise(['status' => 'published'], $contributor);
    }

    public function test_contributor_can_only_choose_draft_or_review(): void
    {
        $contributor = $this->userWithRole(Permissions::CONTRIBUTOR);

        $this->assertSame([ArticleStatus::Draft, ArticleStatus::InReview], $this->workflow->allowedStatuses($contributor));
    }

    public function test_observer_blocks_publishing_by_unauthorised_users_even_without_the_form(): void
    {
        $journalist = $this->userWithRole(Permissions::JOURNALIST);
        $article = Article::factory()->create(['author_id' => $journalist->id, 'category_id' => $this->categoryId]);

        $this->actingAs($journalist);
        $this->expectException(AuthorizationException::class);

        $article->update(['status' => ArticleStatus::Published]);
    }

    public function test_authors_cannot_edit_published_or_others_articles(): void
    {
        $journalist = $this->userWithRole(Permissions::JOURNALIST);
        $own = Article::factory()->create(['author_id' => $journalist->id, 'category_id' => $this->categoryId]);
        $ownPublished = Article::factory()->published()->create(['author_id' => $journalist->id, 'category_id' => $this->categoryId]);
        $other = Article::factory()->create(['category_id' => $this->categoryId]);

        $this->assertTrue($journalist->can('update', $own));
        $this->assertFalse($journalist->can('update', $ownPublished));
        $this->assertFalse($journalist->can('update', $other));
        $this->assertFalse($journalist->can('delete', $ownPublished));
    }

    public function test_editor_can_publish_and_approval_is_recorded(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR);
        $article = Article::factory()->inReview()->create(['category_id' => $this->categoryId]);

        $this->actingAs($editor);
        $this->workflow->publishNow($article, $editor);

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertSame($editor->id, $article->approved_by);
        $this->assertTrue($article->isPublished());
    }

    public function test_publishing_with_a_future_date_becomes_scheduled(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR);

        $data = $this->workflow->normalise(['status' => 'published', 'published_at' => now()->addDay()->toDateTimeString()], $editor);

        $this->assertSame(ArticleStatus::Scheduled, $data['status']);
    }

    public function test_scheduling_requires_a_future_date(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR);

        $this->expectException(ValidationException::class);
        $this->workflow->normalise(['status' => 'scheduled', 'published_at' => now()->subHour()->toDateTimeString()], $editor);
    }

    public function test_scheduled_articles_are_released_by_the_scheduler(): void
    {
        $due = Article::factory()->create(['status' => ArticleStatus::Scheduled, 'published_at' => now()->subMinute(), 'category_id' => $this->categoryId]);
        $future = Article::factory()->create(['status' => ArticleStatus::Scheduled, 'published_at' => now()->addHour(), 'category_id' => $this->categoryId]);

        $this->artisan('articles:publish-scheduled')->assertSuccessful();

        $this->assertSame(ArticleStatus::Published, $due->fresh()->status);
        $this->assertSame(ArticleStatus::Scheduled, $future->fresh()->status);
    }

    public function test_updates_create_revisions_and_audit_entries(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR);
        $this->actingAs($editor);
        $article = Article::factory()->create(['title' => 'Original headline', 'category_id' => $this->categoryId]);

        $article->update(['title' => 'Updated headline']);

        $revision = $article->revisions()->first();
        $this->assertSame('Original headline', $revision->title);
        $this->assertSame($editor->id, $revision->user_id);
        $this->assertDatabaseHas('audit_logs', ['auditable_id' => $article->id, 'event' => 'updated', 'user_id' => $editor->id]);
    }

    public function test_body_html_is_sanitised_and_reading_time_calculated(): void
    {
        $article = Article::factory()->create([
            'category_id' => $this->categoryId,
            'body' => '<p>Hello</p><script>alert(1)</script><img src="x" onerror="alert(2)"><p>'.str_repeat('word ', 440).'</p>',
        ]);

        $this->assertStringNotContainsString('<script', $article->body);
        $this->assertStringNotContainsString('onerror', $article->body);
        $this->assertSame(2, $article->reading_time);
    }
}
