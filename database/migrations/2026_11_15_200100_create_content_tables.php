<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('variants')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->string('credit')->nullable();
            $table->boolean('rights_confirmed')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 9)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_nav')->default(true);
            $table->boolean('is_football')->default(false)->index();
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->index(['parent_id', 'position']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->json('insight_blocks')->nullable();
            $table->string('type', 32)->default('news');
            $table->string('insight_type', 40)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            // FKs for video_id / fixture_id are added once those tables exist.
            $table->unsignedBigInteger('video_id')->nullable();
            $table->unsignedBigInteger('fixture_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->timestamp('breaking_until')->nullable();
            $table->boolean('is_sponsored')->default(false);
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_url')->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);
            $table->unsignedSmallInteger('reading_time')->default(1);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['subcategory_id', 'status', 'published_at']);
            $table->index(['author_id', 'status', 'published_at']);
            $table->index(['type', 'status', 'published_at']);
            $table->index(['is_featured', 'status', 'published_at']);
            $table->index(['is_breaking', 'status']);
            $table->index('view_count');
            $table->index('fixture_id');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('articles', function (Blueprint $table) {
                $table->fullText(['title', 'excerpt', 'body']);
            });
        }

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
            $table->index('tag_id');
        });

        Schema::create('article_media', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['article_id', 'media_id']);
        });

        Schema::create('article_related', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('articles')->cascadeOnDelete();
            $table->primary(['article_id', 'related_article_id']);
        });

        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->json('insight_blocks')->nullable();
            $table->string('status', 20);
            $table->string('note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['article_id', 'created_at']);
        });

        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unique(['article_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        foreach (['article_views', 'article_revisions', 'article_related', 'article_media', 'article_tag', 'articles', 'tags', 'categories', 'media'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
