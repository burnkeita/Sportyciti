<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use Auditable, HasFactory, HasSlug;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'color', 'position',
        'is_active', 'show_in_nav', 'is_football', 'seo_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_nav' => 'boolean',
            'is_football' => 'boolean',
        ];
    }

    /** IDs of all categories flagged as football coverage (feeds the Football hub). */
    public static function footballIds(): array
    {
        return Cache::remember('categories.football_ids', now()->addHour(), fn () => static::where('is_football', true)->pluck('id')->all());
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public static function flushCache(): void
    {
        Cache::forget('nav.categories');
        Cache::forget('categories.football_ids');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /** IDs of this category and its direct children (two-level hierarchy). */
    public function familyIds(): array
    {
        return [$this->id, ...$this->children()->pluck('id')->all()];
    }

    /** Ancestors from root to this category (for breadcrumbs). */
    public function lineage(): Collection
    {
        $chain = collect([$this]);
        $node = $this;
        $guard = 0;
        while ($node->parent_id && $guard++ < 5) {
            $node = $node->parent;
            $chain->prepend($node);
        }

        return $chain;
    }

    public function url(): string
    {
        return route('categories.show', $this->slug);
    }

    /** Cached navigation tree for the public header. */
    public static function navigation(): Collection
    {
        return Cache::remember('nav.categories', now()->addHour(), fn () => static::query()
            ->active()->roots()->where('show_in_nav', true)
            ->with(['children' => fn ($q) => $q->active()->where('show_in_nav', true)])
            ->orderBy('position')
            ->get());
    }
}
