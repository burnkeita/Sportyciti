<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = ['competition_id', 'year', 'name', 'starts_on', 'ends_on', 'is_current'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class)->orderBy('group_name')->orderBy('rank');
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    /** "2026/27" for split-year leagues, "2026" when start and end fall in the same year. */
    public static function labelFor(int $year, ?string $start = null, ?string $end = null): string
    {
        if ($start && $end && substr($start, 0, 4) === substr($end, 0, 4)) {
            return (string) $year;
        }

        return $year.'/'.substr((string) ($year + 1), -2);
    }
}
