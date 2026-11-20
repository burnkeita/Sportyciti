<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\Category;
use App\Support\Permissions;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleCmsTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->category = Category::whereNull('parent_id')->firstOrFail();
    }

    public function test_contributor_creates_a_draft_and_cannot_publish_via_the_form(): void
    {
        $contributor = $this->userWithRole(Permissions::CONTRIBUTOR);
        $this->actingAs($contributor);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Hearts of Oak unveil new coach',
                'body' => '<p>Story body.</p>',
                'category_id' => $this->category->id,
                'type' => 'news',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Hearts of Oak unveil new coach')->firstOrFail();
        $this->assertSame('hearts-of-oak-unveil-new-coach', $article->slug);
        $this->assertSame($contributor->id, $article->author_id);
        $this->assertSame(ArticleStatus::Draft, $article->status);

        // Attempting to publish through the edit form is rejected server-side.
        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['status' => 'published'])
            ->call('save')
            ->assertHasErrors();

        $this->assertSame(ArticleStatus::Draft, $article->fresh()->status);
    }

    public function test_editor_publishes_from_the_form(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR);
        $this->actingAs($editor);

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Black Queens qualify',
                'body' => '<p>Report.</p>',
                'category_id' => $this->category->id,
                'type' => 'news',
                'status' => 'published',
                'author_id' => $editor->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Black Queens qualify')->firstOrFail();
        $this->assertTrue($article->isPublished());
        $this->get($article->url())->assertOk();
    }

    public function test_insight_type_is_required_for_insight_articles(): void
    {
        $this->actingAs($this->userWithRole(Permissions::EDITOR));

        Livewire::test(CreateArticle::class)
            ->fillForm([
                'title' => 'Tactical breakdown',
                'body' => '<p>Analysis.</p>',
                'category_id' => $this->category->id,
                'type' => 'insight',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasFormErrors(['insight_type' => 'required']);
    }
}
