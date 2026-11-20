<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Enums\CommentStatus;
use App\Enums\InsightType;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use Auditable, HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'insight_blocks', 'type', 'insight_type', 'status',
        'category_id', 'subcategory_id', 'author_id', 'featured_media_id', 'og_media_id', 'video_id', 'fixture_id',
        'published_at', 'review_notes', 'is_featured', 'is_breaking', 'breaking_until',
        'is_sponsored', 'sponsor_name', 'sponsor_url', 'allow_comments',
        'seo_title', 'meta_description', 'canonical_url', 'noindex',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'type' => ArticleType::class,
            'insight_type' => InsightType::class,
            'insight_blocks' => 'array',
            'published_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'breaking_until' => 'datetime',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'is_sponsored' => 'boolean',
            'allow_comments' => 'boolean',
            'noindex' => 'boolean',
        ];
    }

    // ---- Relationships ---------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'article_media')->withPivot('position')->orderByPivot('position');
    }

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'article_related', 'article_id', 'related_article_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class)->latest('created_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', CommentStatus::Approved);
    }

    public function dailyViews(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    // ---- Scopes ----------------------------------------------------------

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published)->where('published_at', '<=', now());
    }

    public function scopeInCategory(Builder $query, Category $category): void
    {
        $ids = $category->familyIds();
        $query->where(fn ($q) => $q->whereIn('category_id', $ids)->orWhereIn('subcategory_id', $ids));
    }

    public function scopeBreaking(Builder $query): void
    {
        $query->published()->where('is_breaking', true)
            ->where(fn ($q) => $q->whereNull('breaking_until')->orWhere('breaking_until', '>', now()));
    }

    public function scopeForCard(Builder $query): void
    {
        $query->select([
            'id', 'title', 'slug', 'excerpt', 'type', 'insight_type', 'category_id', 'subcategory_id', 'author_id',
            'featured_media_id', 'published_at', 'is_sponsored', 'sponsor_name', 'reading_time', 'view_count', 'is_breaking',
        ])->with(['category:id,name,slug,color', 'subcategory:id,name,slug,color', 'featuredMedia', 'author:id,name,slug,is_public_author']);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $term = trim($term);
        $driver = $query->getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true) && mb_strlen($term) >= 3) {
            $query->whereFullText(['title', 'excerpt', 'body'], $term);

            return;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
        $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('excerpt', 'like', $like));
    }

    // ---- Helpers ---------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published && $this->published_at && $this->published_at->lte(now());
    }

    public function url(): string
    {
        return route('articles.show', $this->slug);
    }

    public function displayCategory(): ?Category
    {
        return $this->subcategory ?? $this->category;
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->meta_description ?: ($this->excerpt ?: Str::limit(strip_tags((string) $this->body), 155));
    }

    public function socialImage(): ?Media
    {
        return $this->ogMedia ?? $this->featuredMedia;
    }

    public static function estimateReadingTime(?string $html): int
    {
        $words = str_word_count(strip_tags((string) $html));

        return max(1, (int) ceil($words / 220));
    }

    public function wasUpdatedAfterPublishing(): bool
    {
        return $this->published_at && $this->updated_at && $this->updated_at->gt($this->published_at->copy()->addMinutes(5));
    }
}
