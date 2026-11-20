<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value site settings, cached as a single array.
 * Usage: Setting::get('site.tagline'), Setting::put('site.tagline', '...').
 */
class Setting extends Model
{
    use Auditable;

    protected $fillable = ['group', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    public static function allCached(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allCached()[$key] ?? null;

        return $value ?? $default;
    }

    public static function put(string $key, mixed $value, ?string $group = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group ?? strtok($key, '.')]);
        Cache::forget('settings.all');
    }
}
