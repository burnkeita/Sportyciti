<?php

namespace App\Enums;

/**
 * Normalised match status codes (aligned with API-Football short codes,
 * which most providers can be mapped onto).
 */
final class FixtureStatus
{
    public const LIVE = ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'];

    public const FINISHED = ['FT', 'AET', 'PEN', 'AWD', 'WO'];

    public const UPCOMING = ['TBD', 'NS'];

    public const OFF = ['PST', 'CANC', 'ABD'];

    public static function isLive(?string $status): bool
    {
        return in_array($status, self::LIVE, true);
    }

    public static function isFinished(?string $status): bool
    {
        return in_array($status, self::FINISHED, true);
    }

    public static function label(?string $status): string
    {
        return match ($status) {
            'TBD' => 'Time TBD',
            'NS' => 'Not started',
            '1H' => 'First half',
            'HT' => 'Half-time',
            '2H' => 'Second half',
            'ET' => 'Extra time',
            'BT' => 'Break',
            'P' => 'Penalties',
            'SUSP' => 'Suspended',
            'INT' => 'Interrupted',
            'FT' => 'Full-time',
            'AET' => 'After extra time',
            'PEN' => 'After penalties',
            'PST' => 'Postponed',
            'CANC' => 'Cancelled',
            'ABD' => 'Abandoned',
            'AWD' => 'Awarded',
            'WO' => 'Walkover',
            'LIVE' => 'Live',
            default => (string) $status,
        };
    }
}
