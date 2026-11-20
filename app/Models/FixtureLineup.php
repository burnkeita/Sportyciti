<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixtureLineup extends Model
{
    protected $fillable = ['fixture_id', 'team_id', 'formation', 'coach_name', 'starting_xi', 'substitutes'];

    protected function casts(): array
    {
        return [
            'starting_xi' => 'array',
            'substitutes' => 'array',
        ];
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
