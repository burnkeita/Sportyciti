<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Article */
class ArticleResource extends JsonResource
{
    public function __construct($resource, private readonly bool $full = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => $this->url(),
            'excerpt' => $this->excerpt,
            'type' => $this->type?->value,
            'insight_type' => $this->insight_type?->value,
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            'subcategory' => $this->subcategory ? ['name' => $this->subcategory->name, 'slug' => $this->subcategory->slug] : null,
            'author' => $this->author ? ['name' => $this->author->name, 'url' => $this->author->url()] : null,
            'image' => $this->featuredMedia ? [
                'url' => $this->featuredMedia->url('lg'),
                'alt' => $this->featuredMedia->altText(),
                'credit' => $this->featuredMedia->credit,
            ] : null,
            'is_sponsored' => (bool) $this->is_sponsored,
            'reading_time' => $this->reading_time,
            'published_at' => $this->published_at?->toIso8601String(),
            $this->mergeWhen($this->full, fn () => [
                'body' => $this->body,
                'tags' => $this->tags->map(fn ($t) => ['name' => $t->name, 'slug' => $t->slug])->values(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ]),
        ];
    }
}
