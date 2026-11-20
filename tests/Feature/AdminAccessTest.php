<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_registered_readers_cannot_access_admin(): void
    {
        $reader = User::factory()->create();

        $this->actingAs($reader)->get('/admin')->assertForbidden();
    }

    public function test_inactive_staff_cannot_access_admin(): void
    {
        $editor = $this->userWithRole(Permissions::EDITOR, ['is_active' => false]);

        $this->actingAs($editor)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_open_every_admin_screen(): void
    {
        $admin = $this->userWithRole(Permissions::SUPER_ADMIN);
        $article = Article::factory()->create(['category_id' => Category::whereNull('parent_id')->value('id'), 'author_id' => $admin->id]);

        $paths = [
            '/admin', '/admin/articles', '/admin/articles/create', "/admin/articles/{$article->id}/edit",
            '/admin/categories', '/admin/tags', '/admin/media', '/admin/comments',
            '/admin/competitions', '/admin/fixtures', '/admin/teams', '/admin/players', '/admin/transfers', '/admin/sync-logs',
            '/admin/sports-api-settings', '/admin/videos', '/admin/galleries', '/admin/newsletter-subscribers',
            '/admin/advertisements', '/admin/ad-placements', '/admin/homepage-sections', '/admin/site-settings',
            '/admin/users', '/admin/roles', '/admin/audit-logs',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_contributor_sees_only_newsroom_screens_they_are_allowed(): void
    {
        $contributor = $this->userWithRole(Permissions::CONTRIBUTOR);

        $this->actingAs($contributor)->get('/admin/articles')->assertOk();
        $this->actingAs($contributor)->get('/admin/articles/create')->assertOk();

        foreach (['/admin/categories', '/admin/users', '/admin/roles', '/admin/site-settings', '/admin/advertisements', '/admin/competitions', '/admin/audit-logs'] as $path) {
            $this->actingAs($contributor)->get($path)->assertForbidden();
        }
    }

    public function test_authors_cannot_open_other_authors_articles(): void
    {
        $journalist = $this->userWithRole(Permissions::JOURNALIST);
        $other = Article::factory()->create(['category_id' => Category::whereNull('parent_id')->value('id')]);

        $this->actingAs($journalist)->get("/admin/articles/{$other->id}/edit")->assertNotFound();
    }

    public function test_moderator_can_moderate_comments_only(): void
    {
        $moderator = $this->userWithRole(Permissions::MODERATOR);

        $this->actingAs($moderator)->get('/admin/comments')->assertOk();
        $this->actingAs($moderator)->get('/admin/articles')->assertForbidden();
    }

    public function test_staff_must_set_up_two_factor_when_required(): void
    {
        // Filament registers the 2FA enforcement at boot, so boot a fresh app with the flag on,
        // exactly as production does with STAFF_MFA_REQUIRED=true.
        putenv('STAFF_MFA_REQUIRED=true');
        $_ENV['STAFF_MFA_REQUIRED'] = $_SERVER['STAFF_MFA_REQUIRED'] = 'true';

        try {
            $this->refreshApplication();
            $this->artisan('migrate');
            $this->seedSite();

            $editor = $this->userWithRole(Permissions::EDITOR);

            $this->actingAs($editor)->get('/admin/articles')
                ->assertRedirectContains('multi-factor-authentication');

            $editor->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
            $this->actingAs($editor->fresh())->get('/admin/articles')->assertOk();
        } finally {
            putenv('STAFF_MFA_REQUIRED');
            unset($_ENV['STAFF_MFA_REQUIRED'], $_SERVER['STAFF_MFA_REQUIRED']);
        }
    }

    public function test_administrator_cannot_manage_roles(): void
    {
        $admin = $this->userWithRole(Permissions::ADMIN);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/roles')->assertForbidden();
    }
}
