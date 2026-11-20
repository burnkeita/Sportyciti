<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'country_id', 'name', 'slug', 'short_name', 'code', 'logo_url', 'venue_name', 'venue_city',
        'founded', 'is_national', 'provider', 'external_id',
    ];

    protected function casts(): array
    {
        return ['is_national' => 'boolean'];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    /** All fixtures involving this team. */
    public function fixtures(): Builder
    {
        return Fixture::query()->where(fn ($q) => $q->where('home_team_id', $this->id)->orWhere('away_team_id', $this->id));
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_team_id');
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_team_id');
    }

    public function url(): string
    {
        return route('football.teams.show', $this->slug);
    }

    public function displayName(): string
    {
        return $this->short_name ?: $this->name;
    }
}
