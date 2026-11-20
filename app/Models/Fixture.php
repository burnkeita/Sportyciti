<?php

namespace App\Models;

use App\Enums\FixtureStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id', 'season_id', 'home_team_id', 'away_team_id', 'kickoff_at', 'status', 'status_long', 'elapsed',
        'home_score', 'away_score', 'home_ht_score', 'away_ht_score', 'home_pen_score', 'away_pen_score',
        'round', 'venue_name', 'venue_city', 'referee', 'is_featured', 'provider', 'external_id', 'details_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'details_synced_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(FixtureEvent::class)->orderBy('sort_order');
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(FixtureLineup::class);
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(FixtureStatistic::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeLive(Builder $query): void
    {
        $query->whereIn('status', FixtureStatus::LIVE);
    }

    public function scopeFinished(Builder $query): void
    {
        $query->whereIn('status', FixtureStatus::FINISHED);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->whereIn('status', FixtureStatus::UPCOMING)->where('kickoff_at', '>=', now()->subHours(3));
    }

    public function scopeForTeam(Builder $query, int $teamId): void
    {
        $query->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId));
    }

    public function scopeWithCardRelations(Builder $query): void
    {
        $query->with([
            'homeTeam:id,name,short_name,slug,logo_url',
            'awayTeam:id,name,short_name,slug,logo_url',
            'competition:id,name,short_name,slug,logo_url,country_id,provider',
        ]);
    }

    public function isLive(): bool
    {
        return FixtureStatus::isLive($this->status);
    }

    public function isFinished(): bool
    {
        return FixtureStatus::isFinished($this->status);
    }

    public function hasStarted(): bool
    {
        return $this->isLive() || $this->isFinished();
    }

    public function statusLabel(): string
    {
        return FixtureStatus::label($this->status);
    }

    public function isDemo(): bool
    {
        return $this->provider === 'demo';
    }

    public function url(): string
    {
        return route('football.matches.show', ['fixture' => $this->id, 'slug' => $this->slugText()]);
    }

    public function slugText(): string
    {
        return Str::slug(($this->homeTeam?->name ?? 'home').' vs '.($this->awayTeam?->name ?? 'away'));
    }

    /** Result letter (W/D/L) from the perspective of the given team, if finished. */
    public function resultFor(int $teamId): ?string
    {
        if (! $this->isFinished() || $this->home_score === null) {
            return null;
        }

        $isHome = $this->home_team_id === $teamId;
        $for = $isHome ? $this->home_score : $this->away_score;
        $against = $isHome ? $this->away_score : $this->home_score;

        if ($for === $against && $this->home_pen_score !== null) {
            $for = $isHome ? $this->home_pen_score : $this->away_pen_score;
            $against = $isHome ? $this->away_pen_score : $this->home_pen_score;
        }

        return $for > $against ? 'W' : ($for < $against ? 'L' : 'D');
    }
}
