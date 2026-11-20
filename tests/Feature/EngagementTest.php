<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Livewire\BookmarkButton;
use App\Livewire\Comments;
use App\Livewire\NewsletterForm;
use App\Mail\ConfirmNewsletterSubscription;
use App\Models\AdPlacement;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\Ads\AdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EngagementTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->article = Article::factory()->published()->create(['category_id' => Category::whereNull('parent_id')->value('id')]);
    }

    public function test_newsletter_uses_double_opt_in(): void
    {
        Mail::fake();

        Livewire::test(NewsletterForm::class)->set('email', 'Fan@Example.com')->call('subscribe')->assertSet('done', true);

        $subscriber = NewsletterSubscriber::where('email', 'fan@example.com')->firstOrFail();
        $this->assertSame('pending', $subscriber->status);
        Mail::assertQueued(ConfirmNewsletterSubscription::class);

        $this->get(route('newsletter.confirm', $subscriber->token))->assertOk();
        $this->assertSame('confirmed', $subscriber->fresh()->status);

        $this->get(route('newsletter.unsubscribe', $subscriber->token))->assertOk();
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_newsletter_honeypot_silently_drops_bots(): void
    {
        Mail::fake();

        Livewire::test(NewsletterForm::class)->set('email', 'bot@example.com')->set('website', 'http://spam')->call('subscribe');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
        Mail::assertNothingQueued();
    }

    public function test_bookmarks_require_login_and_toggle(): void
    {
        Livewire::test(BookmarkButton::class, ['articleId' => $this->article->id])->call('toggle')->assertRedirect(route('login'));

        $reader = User::factory()->create();
        Livewire::actingAs($reader)->test(BookmarkButton::class, ['articleId' => $this->article->id])
            ->call('toggle')->assertSet('saved', true)
            ->call('toggle')->assertSet('saved', false);

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_verified_readers_can_comment_and_comments_are_held_for_moderation(): void
    {
        $reader = User::factory()->create();

        Livewire::actingAs($reader)->test(Comments::class, ['articleId' => $this->article->id])
            ->set('body', 'Great piece <b>really</b>')
            ->call('post')
            ->assertHasNoErrors()
            ->assertSet('notice', fn ($n) => str_contains($n, 'moderator'));

        $comment = $this->article->comments()->firstOrFail();
        $this->assertSame(CommentStatus::Pending, $comment->status);
        $this->assertSame('Great piece really', $comment->body);
    }

    public function test_unverified_readers_cannot_comment(): void
    {
        $reader = User::factory()->unverified()->create();

        Livewire::actingAs($reader)->test(Comments::class, ['articleId' => $this->article->id])
            ->set('body', 'Hello')->call('post')->assertForbidden();
    }

    public function test_reader_account_pages_and_interests(): void
    {
        $reader = User::factory()->create();
        $category = Category::where('slug', 'black-stars')->firstOrFail();

        $this->actingAs($reader)->get('/account')->assertOk();
        $this->actingAs($reader)->get('/account/saved')->assertOk();
        $this->actingAs($reader)->put('/account/interests', ['categories' => [$category->id]])->assertRedirect('/account');

        $this->assertTrue($reader->favouriteCategories()->whereKey($category->id)->exists());
    }

    public function test_ad_rotation_impressions_and_click_tracking(): void
    {
        $placement = AdPlacement::where('key', 'sidebar')->firstOrFail();
        $ad = Advertisement::create([
            'ad_placement_id' => $placement->id, 'name' => 'Test campaign', 'type' => 'image',
            'image_path' => 'ads/test.png', 'target_url' => 'https://example.com/landing', 'is_active' => true,
        ]);
        Advertisement::create([
            'ad_placement_id' => $placement->id, 'name' => 'Expired', 'type' => 'image', 'image_path' => 'ads/x.png',
            'target_url' => 'https://example.com', 'is_active' => true, 'ends_at' => now()->subDay(),
        ]);

        $service = app(AdService::class);
        $this->assertTrue($service->forPlacement('sidebar')->is($ad), 'Expired campaigns must not be served.');

        $service->recordImpression($ad);
        $service->recordImpression($ad);
        $this->assertSame(2, $service->flushImpressions());
        $this->assertSame(2, $ad->fresh()->impressions);

        $this->get(route('ads.click', $ad))->assertRedirect('https://example.com/landing');
        $this->assertSame(1, $ad->fresh()->clicks);
    }
}
