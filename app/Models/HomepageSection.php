<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class HomepageSection extends Model
{
    public const TYPES = [
        'featured' => 'Featured stories (hero)',
        'latest' => 'Latest headlines',
        'category' => 'Category block',
        'insights' => 'SportyCiti Insights',
        'previews' => 'Featured match previews',
        'scores' => 'Live scores & upcoming fixtures',
        'trending' => 'Trending & most read',
        'videos' => 'Videos & multimedia',
        'transfers' => 'Transfer centre',
        'newsletter' => 'Newsletter sign-up',
        'ad' => 'Advertisement slot',
    ];

    protected $fillable = ['title', 'type', 'category_id', 'item_limit', 'layout', 'options', 'position', 'is_active'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('home.sections'));
        static::deleted(fn () => Cache::forget('home.sections'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
