<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisementDailyStat extends Model
{
    public $timestamps = false;

    protected $fillable = ['advertisement_id', 'date', 'impressions', 'clicks'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }
}
