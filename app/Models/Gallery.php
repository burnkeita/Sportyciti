<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'title', 'slug', 'description', 'cover_media_id', 'category_id', 'photographer',
        'rights_confirmed', 'status', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rights_confirmed' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->where('rights_confirmed', true)->where('published_at', '<=', now());
    }

    public function url(): string
    {
        return route('galleries.show', $this->slug);
    }

    public function coverMedia(): ?Media
    {
        return $this->cover ?? $this->items->first()?->media;
    }
}
