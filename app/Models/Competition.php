<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Competition extends Model
{
    use Auditable, HasFactory, HasSlug;

    protected $fillable = [
        'sport_id', 'country_id', 'category_id', 'name', 'slug', 'short_name', 'type', 'logo_url', 'provider', 'external_id',
        'current_season_year', 'is_active', 'is_featured', 'sync_enabled', 'has_standings', 'position', 'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sync_enabled' => 'boolean',
            'has_standings' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderByDesc('year');
    }

    public function currentSeason(): HasOne
    {
        return $this->hasOne(Season::class)->where('is_current', true);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('position')->orderBy('name');
    }

    public function scopeSyncable(Builder $query): void
    {
        $query->where('is_active', true)->where('sync_enabled', true)->whereNotNull('external_id')->whereNotNull('current_season_year');
    }

    public function url(): string
    {
        return route('football.competitions.show', $this->slug);
    }

    public function displayName(): string
    {
        return $this->short_name ?: $this->name;
    }
}
