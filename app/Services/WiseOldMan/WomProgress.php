<?php

namespace App\Services\WiseOldMan;

use App\Models\WomPlayer;
use Illuminate\Support\Facades\Cache;

/**
 * The instance-wide numbers on /progress, over the members whose linked WOM
 * profile is active and whose owner has not hidden it. The stats are cached:
 * they walk every member's snapshot maps.
 */
class WomProgress
{
    public const STATS_CACHE_SECONDS = 600;

    public function __construct(private WomMetricGains $gains) {}

    /**
     * @return array{active_members: int, week_exp_gained: int, week_levels_gained: int, week_boss_kills: int}
     */
    public function stats(): array
    {
        return Cache::remember(WomCache::key('progress:stats'), self::STATS_CACHE_SECONDS, function (): array {
            $accountIds = self::population();
            $week = $this->gains->totals($accountIds, 'week');

            return [
                'active_members' => count($accountIds),
                'week_exp_gained' => $week['exp'],
                'week_levels_gained' => $week['levels'],
                'week_boss_kills' => $week['boss_kills'],
            ];
        });
    }

    /**
     * The account ids /progress counts.
     *
     * @return list<int>
     */
    public static function population(): array
    {
        return WomPlayer::active()->visibleOnProgress()->pluck('account_id')->map(fn ($id): int => (int) $id)->all();
    }
}
