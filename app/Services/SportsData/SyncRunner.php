<?php

namespace App\Services\SportsData;

use App\Models\SyncLog;
use App\Services\SportsData\Exceptions\RateLimitedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Wraps every sync operation with logging, timing and a simple circuit breaker:
 * after N consecutive failures the provider is paused for a cool-down period so a
 * failing upstream does not burn quota or flood logs.
 */
class SyncRunner
{
    public function __construct(private readonly FootballSyncService $sync) {}

    public function service(): FootballSyncService
    {
        return $this->sync;
    }

    /**
     * @param  callable(FootballSyncService): int  $operation
     */
    public function run(string $resource, callable $operation, array $context = []): SyncLog
    {
        $provider = $this->sync->provider()->key();
        $circuitKey = "sportsdata.circuit.{$provider}";

        if (Cache::has("{$circuitKey}.open")) {
            return $this->log($provider, $resource, 'skipped', 0, 0, 'Provider paused after repeated failures (circuit open).', $context);
        }

        $start = hrtime(true);

        try {
            $records = $operation($this->sync);
            Cache::forget("{$circuitKey}.failures");
            Cache::put("sportsdata.last_success.{$provider}", now()->toIso8601String(), now()->addDays(7));

            return $this->log($provider, $resource, 'success', $records, $this->elapsed($start), null, $context);
        } catch (RateLimitedException $e) {
            return $this->log($provider, $resource, 'skipped', 0, $this->elapsed($start), $e->getMessage(), $context + ['retry_after' => $e->retryAfter]);
        } catch (Throwable $e) {
            $failures = Cache::increment("{$circuitKey}.failures");

            if ($failures >= (int) config('sportsdata.circuit_failures')) {
                Cache::put("{$circuitKey}.open", true, (int) config('sportsdata.circuit_cooldown'));
                Cache::forget("{$circuitKey}.failures");
            }

            Log::warning('Sports data sync failed', ['provider' => $provider, 'resource' => $resource, 'error' => $e->getMessage()]);

            return $this->log($provider, $resource, 'failed', 0, $this->elapsed($start), $e->getMessage(), $context);
        }
    }

    private function elapsed(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }

    private function log(string $provider, string $resource, string $status, int $records, int $ms, ?string $message, array $context): SyncLog
    {
        return SyncLog::create([
            'provider' => $provider,
            'resource' => $resource,
            'status' => $status,
            'records' => $records,
            'duration_ms' => $ms,
            'message' => $message ? mb_substr($message, 0, 2000) : null,
            'context' => $context ?: null,
        ]);
    }
}
