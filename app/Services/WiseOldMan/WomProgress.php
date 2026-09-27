<?php

namespace App\Services\WiseOldMan;

use App\Models\WomPlayer;
use Illuminate\Support\Facades\Cache;

/**
 * The instance-wide numbers on /progress, over the members whose linked WOM
 * profile is active and whose owner has not hidden it. Both reads are cached:
 * the stats feed every member's dashboard-adjacent pages, and the series
 * walks every snapshot in its range.
 */
class WomProgress
{
    public const STATS_CACHE_SECONDS = 600;

    public const SERIES_CACHE_SECONDS = 1800;

    public function __construct(private WomSnapshotHistory $history) {}

    /**
     * @return array{active_members: int, total_ehp: float, total_ehb: float, week_exp_gained: int}
     */
    public function stats(): array
    {
        return Cache::remember(WomCache::key('progress:stats'), self::STATS_CACHE_SECONDS, function (): array {
            $players = WomPlayer::active()->visibleOnProgress()->get(['account_id', 'ehp', 'ehb']);

            $weekGained = $this->history->gained($players->pluck('account_id')->all(), 'week');

            return [
                'active_members' => $players->count(),
                'total_ehp' => round((float) $players->sum('ehp'), 2),
                'total_ehb' => round((float) $players->sum('ehb'), 2),
                'week_exp_gained' => (int) array_sum(array_column($weekGained, 'exp')),
            ];
        });
    }

    /**
     * What the members gained in total since the range began, one point per
     * day, oldest first. Each account is measured from its last snapshot at or
     * before the range start, or — when its history begins inside the range —
     * from its first snapshot, so a newly linked member adds its gains rather
     * than a step of its whole XP. A member without a point on some day keeps
     * its last gain, so the total never dips because one missed a sync.
     *
     * @return list<array{t: int, exp: int, ehp: float, ehb: float}>
     */
    public function instanceSeries(string $range): array
    {
        $range = array_key_exists($range, WomSnapshotHistory::RANGES) ? $range : 'year';

        return Cache::remember(
            WomCache::key("progress:series:{$range}"),
            self::SERIES_CACHE_SECONDS,
            fn (): array => $this->history->downsample($this->cumulativeGains($range)),
        );
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

    /**
     * Streams the range's snapshots in date order, holding only one baseline
     * and one running gain per account.
     *
     * @return list<array{t: int, exp: int, ehp: float, ehb: float}>
     */
    private function cumulativeGains(string $range): array
    {
        $accountIds = self::population();
        $from = $this->history->rangeStart($range);

        $baseline = $from === null ? [] : $this->history->lastOnOrBefore($accountIds, $from);
        $gains = [];
        $sum = ['exp' => 0, 'ehp' => 0.0, 'ehb' => 0.0];
        $series = [];
        $date = null;

        foreach ($this->history->points($accountIds, $from) as $row) {
            if ($date !== null && $row['date'] !== $date) {
                $series[] = $this->point($date, $sum);
            }
            $date = $row['date'];

            $accountId = $row['account_id'];
            $baseline[$accountId] ??= $row;
            $previous = $gains[$accountId] ?? ['exp' => 0, 'ehp' => 0.0, 'ehb' => 0.0];

            foreach (['exp', 'ehp', 'ehb'] as $field) {
                $gain = $row[$field] - $baseline[$accountId][$field];
                $sum[$field] += $gain - $previous[$field];
                $gains[$accountId][$field] = $gain;
            }
        }

        if ($date !== null) {
            $series[] = $this->point($date, $sum);
        }

        return $series;
    }

    /**
     * @param  array{exp: int, ehp: float, ehb: float}  $sum
     * @return array{t: int, exp: int, ehp: float, ehb: float}
     */
    private function point(string $date, array $sum): array
    {
        return [
            't' => $this->history->timestamp($date),
            'exp' => (int) $sum['exp'],
            'ehp' => round((float) $sum['ehp'], 4),
            'ehb' => round((float) $sum['ehb'], 4),
        ];
    }
}
