<?php

namespace App\Services\WiseOldMan;

use Illuminate\Support\Facades\Cache;

/**
 * A generation counter for the cached /progress and leaderboard reads. Every
 * key includes the current version, so bumping it retires them all at once
 * when membership of the population changes (an owner hides an account,
 * withdraws consent, or the instance is reset). Plain syncs rely on the TTL.
 */
class WomCache
{
    private const VERSION_KEY = 'wom:cache:version';

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /** A cache key in the current generation. */
    public static function key(string $key): string
    {
        return 'wom:v'.self::version().':'.$key;
    }

    public static function bump(): void
    {
        // increment() needs an existing value on the database and array stores.
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
    }
}
