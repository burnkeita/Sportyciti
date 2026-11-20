<?php

namespace App\Services\SportsData;

use App\Models\Setting;
use App\Services\SportsData\Contracts\FootballDataProvider;
use App\Services\SportsData\Exceptions\ProviderException;
use App\Services\SportsData\Providers\ApiFootballProvider;
use App\Services\SportsData\Providers\DemoProvider;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Chooses the active football data provider.
 * Credentials are read from admin settings (encrypted) first, then from env/config.
 */
class ProviderResolver
{
    public function resolve(): FootballDataProvider
    {
        $driver = Setting::get('sportsdata.provider') ?: config('sportsdata.provider');

        if ($driver === ApiFootballProvider::KEY) {
            $key = $this->apiFootballKey();

            if ($key) {
                return new ApiFootballProvider(
                    apiKey: $key,
                    baseUrl: (string) config('sportsdata.api_football.base_url'),
                    timeout: (int) config('sportsdata.api_football.timeout'),
                    requestsPerMinute: (int) (Setting::get('sportsdata.requests_per_minute') ?: config('sportsdata.api_football.requests_per_minute')),
                    quotaReserve: (int) config('sportsdata.api_football.daily_quota_reserve'),
                );
            }

            if (! $this->demoFallbackEnabled()) {
                throw ProviderException::notConfigured();
            }
        }

        if ($driver === DemoProvider::KEY || $this->demoFallbackEnabled()) {
            return new DemoProvider;
        }

        throw ProviderException::notConfigured();
    }

    public function isConfigured(): bool
    {
        try {
            $this->resolve();

            return true;
        } catch (ProviderException) {
            return false;
        }
    }

    public function apiFootballKey(): ?string
    {
        $stored = Setting::get('sportsdata.api_football_key');

        if ($stored) {
            try {
                return Crypt::decryptString($stored);
            } catch (DecryptException) {
                report(new ProviderException('Stored API-Football key could not be decrypted (APP_KEY changed?).'));
            }
        }

        return config('sportsdata.api_football.key') ?: null;
    }

    public function demoFallbackEnabled(): bool
    {
        $setting = Setting::get('sportsdata.demo_fallback');

        return $setting === null ? (bool) config('sportsdata.demo_fallback') : (bool) $setting;
    }
}
