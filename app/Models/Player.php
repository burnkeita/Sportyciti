<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasSlug;

    protected $fillable = [
        'team_id', 'nationality_id', 'name', 'slug', 'position', 'birth_date', 'photo_url', 'bio', 'provider', 'external_id',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class)->latest('reported_at');
    }

    public function url(): string
    {
        return route('football.players.show', $this->slug);
    }
}
