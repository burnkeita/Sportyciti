<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    protected $fillable = [
        'season_id', 'team_id', 'group_name', 'rank', 'played', 'won', 'drawn', 'lost',
        'goals_for', 'goals_against', 'goal_difference', 'points', 'form', 'description', 'home', 'away',
    ];

    protected function casts(): array
    {
        return [
            'home' => 'array',
            'away' => 'array',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
