<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Resolves the visitor's timezone from the `tz` cookie (set client-side from
 * Intl.DateTimeFormat). Used to compute "today" and day boundaries for fixture
 * lists. Exact kickoff times are always rendered client-side in the visitor's
 * local time from an ISO-8601 UTC timestamp.
 */
class VisitorTime
{
    public const DEFAULT = 'Africa/Accra';

    public static function timezone(?Request $request = null): string
    {
        $tz = ($request ?? request())->cookie('tz');

        return is_string($tz) && in_array($tz, timezone_identifiers_list(), true) ? $tz : self::DEFAULT;
    }

    public static function today(?Request $request = null): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone($request))->startOfDay();
    }

    /** UTC bounds of a local calendar day. */
    public static function dayBounds(string $date, ?Request $request = null): array
    {
        $start = CarbonImmutable::parse($date, self::timezone($request))->startOfDay();

        return [$start->utc(), $start->endOfDay()->utc()];
    }
}
