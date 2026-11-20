<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    use Auditable;

    protected $fillable = [
        'ad_placement_id', 'name', 'advertiser', 'type', 'image_path', 'mobile_image_path', 'alt_text',
        'target_url', 'html_code', 'adsense_slot', 'device', 'starts_at', 'ends_at', 'priority', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'ad_placement_id');
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(AdvertisementDailyStat::class);
    }

    public function scopeRunning(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function mobileImageUrl(): ?string
    {
        return $this->mobile_image_path ? Storage::disk('public')->url($this->mobile_image_path) : null;
    }

    public function clickThroughRate(): float
    {
        return $this->impressions > 0 ? round($this->clicks / $this->impressions * 100, 2) : 0.0;
    }
}
