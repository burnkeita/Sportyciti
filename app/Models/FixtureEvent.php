<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixtureEvent extends Model
{
    protected $fillable = [
        'fixture_id', 'team_id', 'minute', 'extra_minute', 'type', 'detail', 'player_name', 'assist_name', 'comments', 'sort_order',
    ];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function minuteLabel(): string
    {
        return $this->minute.($this->extra_minute ? '+'.$this->extra_minute : '')."'";
    }
}
