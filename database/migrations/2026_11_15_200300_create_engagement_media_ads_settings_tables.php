<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Multimedia -------------------------------------------------
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('video_type', 30)->default('interview'); // interview | press_conference | match_analysis | feature | news
            $table->string('source', 20)->default('youtube'); // youtube | upload
            $table->string('youtube_id', 20)->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('rights_confirmed')->default(false);
            $table->string('rights_holder')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft'); // draft | published | archived
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status', 'published_at']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('video_id')->references('id')->on('videos')->nullOnDelete();
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photographer')->nullable();
            $table->boolean('rights_confirmed')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('caption')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['gallery_id', 'position']);
        });

        // ---- Engagement -------------------------------------------------
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 20)->default('pending'); // pending | approved | rejected | spam
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['article_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'article_id']);
        });

        Schema::create('category_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'category_id']);
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('status', 20)->default('pending'); // pending | confirmed | unsubscribed
            $table->string('token', 64)->unique();
            $table->string('source', 40)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        // ---- Advertising ------------------------------------------------
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('size_hint', 40)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_placement_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('advertiser')->nullable();
            $table->string('type', 20)->default('image'); // image | html | adsense
            $table->string('image_path')->nullable();
            $table->string('mobile_image_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('target_url')->nullable();
            $table->text('html_code')->nullable();
            $table->string('adsense_slot', 40)->nullable();
            $table->string('device', 10)->default('all'); // all | desktop | mobile
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->index(['ad_placement_id', 'is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('advertisement_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unique(['advertisement_id', 'date']);
        });

        // ---- Site configuration & audit ---------------------------------
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40)->default('general');
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 40); // featured | latest | category | insights | previews | scores | trending | videos | newsletter | ad
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('item_limit')->default(6);
            $table->string('layout', 20)->default('grid');
            $table->json('options')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'position']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 40);
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['event', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['video_id']);
        });

        foreach (['audit_logs', 'homepage_sections', 'settings', 'advertisement_daily_stats', 'advertisements', 'ad_placements', 'newsletter_subscribers', 'category_user', 'bookmarks', 'comments', 'gallery_items', 'galleries', 'videos'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
