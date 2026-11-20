<?php

namespace App\Services\Ads;

use App\Models\Advertisement;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Chooses which advertisement to show in a placement and buffers impression
 * counts in the cache (flushed to the database every few minutes by ads:flush-stats).
 */
class AdService
{
    private const BUFFER_INDEX = 'ads.impressions.index';

    /** @var array<int, true> ads already rendered in this request (avoid duplicates on one page) */
    private array $rendered = [];

    public function forPlacement(string $key): ?Advertisement
    {
        if (! Setting::get('ads.enabled', true)) {
            return null;
        }

        $candidates = $this->running($key)->reject(fn (Advertisement $ad) => isset($this->rendered[$ad->id]));

        if ($candidates->isEmpty()) {
            return null;
        }

        // Highest priority wins; ties rotate randomly.
        $top = $candidates->max('priority');
        $ad = $candidates->where('priority', $top)->random();
        $this->rendered[$ad->id] = true;

        return $ad;
    }

    public function running(string $key): Collection
    {
        return Cache::remember("ads.placement.{$key}", 300, fn () => Advertisement::query()
            ->running()
            ->whereHas('placement', fn ($q) => $q->where('key', $key)->where('is_active', true))
            ->get());
    }

    public function recordImpression(Advertisement $ad): void
    {
        $key = "ads.impressions.{$ad->id}";
        Cache::add($key, 0, now()->addDay());
        Cache::increment($key);

        $index = Cache::get(self::BUFFER_INDEX, []);
        if (! in_array($ad->id, $index, true)) {
            $index[] = $ad->id;
            Cache::put(self::BUFFER_INDEX, $index, now()->addDay());
        }
    }

    public function recordClick(Advertisement $ad): void
    {
        DB::transaction(function () use ($ad): void {
            Advertisement::whereKey($ad->id)->increment('clicks');
            $this->bumpDaily($ad->id, clicks: 1);
        });
    }

    /** Move buffered impressions into the database. Returns the number of impressions flushed. */
    public function flushImpressions(): int
    {
        $index = Cache::pull(self::BUFFER_INDEX, []);
        $total = 0;

        foreach ($index as $id) {
            $count = (int) Cache::pull("ads.impressions.{$id}", 0);
            if ($count <= 0) {
                continue;
            }
            DB::transaction(function () use ($id, $count): void {
                Advertisement::whereKey($id)->increment('impressions', $count);
                $this->bumpDaily($id, impressions: $count);
            });
            $total += $count;
        }

        return $total;
    }

    public static function flushPlacementCache(): void
    {
        foreach (DB::table('ad_placements')->pluck('key') as $key) {
            Cache::forget("ads.placement.{$key}");
        }
    }

    private function bumpDaily(int $adId, int $impressions = 0, int $clicks = 0): void
    {
        $date = now()->toDateString();
        $updated = DB::table('advertisement_daily_stats')->where('advertisement_id', $adId)->where('date', $date)
            ->update(['impressions' => DB::raw("impressions + {$impressions}"), 'clicks' => DB::raw("clicks + {$clicks}")]);

        if ($updated === 0) {
            DB::table('advertisement_daily_stats')->insert(['advertisement_id' => $adId, 'date' => $date, 'impressions' => $impressions, 'clicks' => $clicks]);
        }
    }
}
