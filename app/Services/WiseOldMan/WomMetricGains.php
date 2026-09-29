<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Support\Experience;
use Illuminate\Support\Facades\Cache;

/**
 * What accounts gained per skill, boss and activity, computed from the daily
 * metric maps in `WomDailySnapshot`. One rule serves the Gains tab and
 * /progress: a metric's gain is the latest snapshot's value minus its
 * baseline, never negative. Each metric has its own baseline
 * ({@see WomSnapshotHistory::metricBaselines()}): its value in the last
 * mapped snapshot on or before the period start, else its first appearance
 * after it, so headline-only snapshots from before the maps were stored
 * never blank a gain. `overall` reads the headline `exp` every snapshot has.
 * A metric that first appears in the latest snapshot of an account with
 * older mapped history is "new": its gain is null and it is never ranked. A
 * metric the latest snapshot lacks is not listed.
 */
class WomMetricGains
{
    /** How long one account's gains are reused (the key also changes on every sync). */
    public const CACHE_SECONDS = 600;

    /** Snapshot map fields by {@see WomMetrics::group()}. */
    private const GROUP_FIELDS = ['skill' => 'skills', 'boss' => 'bosses', 'activity' => 'activities'];

    public function __construct(private WomSnapshotHistory $history) {}

    /**
     * Every metric the account's latest snapshot carries, with its gain over
     * `$period` (`day`, `week`, `month`, `year`). `since` is the earliest date
     * the metric maps are measured from when that lies inside the period (the
     * mapped history is shorter than the period), else null. Skills lead with
     * `overall` (xp = total exp, level = total level).
     *
     * @return array{period: string, since: ?string, skills: list<array{metric: string, label: string, icon: array<string, string>|null, xp: int, level: ?int, gained: ?int, levels_gained: ?int}>, bosses: list<array{metric: string, label: string, icon: array<string, string>|null, kills: int, gained: ?int}>, activities: list<array{metric: string, label: string, icon: array<string, string>|null, score: int, gained: ?int}>}
     */
    public function forAccount(Account $account, string $period): array
    {
        $period = self::period($period);
        $player = $account->womPlayer;

        if (! $player?->isLinked()) {
            return ['period' => $period, 'since' => null, 'skills' => [], 'bosses' => [], 'activities' => []];
        }

        return Cache::remember(
            WomCache::key("gains:{$account->id}:{$period}:".($player->synced_at?->getTimestamp() ?? 0).':'.($player->backfilled_at?->getTimestamp() ?? 0)),
            self::CACHE_SECONDS,
            fn (): array => $this->accountGains($account->id, $period),
        );
    }

    /**
     * What each account gained of one metric over `$period`, biggest first.
     * Accounts that gained nothing, or whose gain is unknown, are left out.
     *
     * @param  list<int>  $accountIds
     * @return array<int, int> account id => gained
     */
    public function forPopulation(array $accountIds, string $period, string $metric): array
    {
        $path = WomMetrics::path(WomMetrics::isValid($metric) ? $metric : 'overall');
        $period = self::period($period);

        $baselines = $path === 'exp'
            ? array_map(fn (array $row): array => ['value' => $row['values']['exp']], $this->history->baseline($accountIds, $period, ['exp']))
            : $this->history->pathBaselines($accountIds, $period, $path);

        $gains = [];
        foreach ($this->history->latest($accountIds, [$path]) as $accountId => $latest) {
            $gained = self::gain($latest['values'][$path], $baselines[$accountId]['value'] ?? null);
            if ($gained > 0) {
                $gains[$accountId] = $gained;
            }
        }

        arsort($gains);

        return $gains;
    }

    /**
     * Every account's positive gains in one group (`skill`, `boss`,
     * `activity`) over `$period`, for boards that rank each metric at once.
     *
     * @param  list<int>  $accountIds
     * @return array<int, array<string, int>> account id => metric => gained
     */
    public function forPopulationGroup(array $accountIds, string $period, string $group): array
    {
        $field = self::GROUP_FIELDS[$group] ?? 'skills';
        $latest = $this->latestMaps($accountIds, $field);
        $baselines = $this->history->metricBaselineValues($latest, self::period($period), $field);

        $gains = [];
        foreach ($latest as $accountId => $map) {
            foreach ((array) $map as $metric => $value) {
                $gained = self::gain($value, $baselines[$accountId][$metric] ?? null);
                if ($gained > 0) {
                    $gains[$accountId][$metric] = $gained;
                }
            }
        }

        return $gains;
    }

    /**
     * The population's summed exp, skill levels and boss kills gained over
     * `$period`, under the same rule (unknown gains count as nothing).
     *
     * @param  list<int>  $accountIds
     * @return array{exp: int, levels: int, boss_kills: int}
     */
    public function totals(array $accountIds, string $period): array
    {
        $period = self::period($period);
        $skills = $this->latestMaps($accountIds, 'skills');
        $bosses = $this->latestMaps($accountIds, 'bosses');
        $skillBaselines = $this->history->metricBaselineValues($skills, $period, 'skills');
        $bossBaselines = $this->history->metricBaselineValues($bosses, $period, 'bosses');

        $totals = ['exp' => (int) array_sum($this->forPopulation($accountIds, $period, 'overall')), 'levels' => 0, 'boss_kills' => 0];

        foreach ($skills as $accountId => $map) {
            foreach ((array) $map as $metric => $xp) {
                $totals['levels'] += self::levelsGained($xp, $skillBaselines[$accountId][$metric] ?? null) ?? 0;
            }
        }

        foreach ($bosses as $accountId => $map) {
            foreach ((array) $map as $metric => $kills) {
                $totals['boss_kills'] += self::gain($kills, $bossBaselines[$accountId][$metric] ?? null) ?? 0;
            }
        }

        return $totals;
    }

    /**
     * One metric's daily values over a chart range (`week`, `month`, `year`,
     * `all`), oldest first, thinned to {@see WomSnapshotHistory::MAX_POINTS}.
     * Days whose snapshot lacks the metric are skipped; an unlinked account
     * has none. An unknown metric reads as `overall`.
     *
     * @return list<array{t: int, v: int}>
     */
    public function series(Account $account, string $metric, string $range): array
    {
        if (! $account->womPlayer?->isLinked()) {
            return [];
        }

        $path = WomMetrics::path(WomMetrics::isValid($metric) ? $metric : 'overall');

        $points = array_map(fn (array $row): array => [
            't' => $this->history->timestamp($row['date']),
            'v' => (int) $row['value'],
        ], $this->history->values($account->id, $path, $this->history->rangeStart($range)));

        return $this->history->downsample($points);
    }

    /** A known gain period, else `week`. */
    public static function period(string $period): string
    {
        return array_key_exists($period, WomSnapshotHistory::PERIODS) ? $period : 'week';
    }

    /**
     * @return array{period: string, since: ?string, skills: list<array<string, mixed>>, bosses: list<array<string, mixed>>, activities: list<array<string, mixed>>}
     */
    private function accountGains(int $accountId, string $period): array
    {
        $latest = $this->history->latest([$accountId], ['exp', 'skills', 'bosses', 'activities'])[$accountId] ?? null;

        if ($latest === null) {
            return ['period' => $period, 'since' => null, 'skills' => [], 'bosses' => [], 'activities' => []];
        }

        $now = $latest['values'];
        $expBaseline = $this->history->baseline([$accountId], $period, ['exp'])[$accountId] ?? $latest;

        $baselines = [];
        foreach (self::GROUP_FIELDS as $field) {
            $baselines[$field] = $this->history->metricBaselines([$accountId], $period, $field)[$accountId] ?? [];
        }

        // The mapped history starts at the earliest baseline; a metric whose
        // baseline is the latest snapshot itself only counts as new when
        // there is older mapped history to be new against.
        $dates = array_merge(...array_map(fn (array $metrics): array => array_column($metrics, 'date'), array_values($baselines)));
        $mappedFrom = $dates === [] ? null : min($dates);
        $then = function (string $field, string $metric) use ($baselines, $latest, $mappedFrom): mixed {
            $baseline = $baselines[$field][$metric] ?? null;

            return $baseline === null || ($baseline['date'] === $latest['date'] && $mappedFrom < $latest['date'])
                ? null
                : $baseline['value'];
        };

        $skills = [];
        foreach ($this->ordered(WomMetrics::SKILLS, $now['skills']) as $metric => $xp) {
            $before = $then('skills', $metric);
            $skills[] = [
                ...$this->describe($metric),
                'xp' => (int) $xp,
                'level' => Experience::levelFor((int) $xp),
                'gained' => self::gain($xp, $before),
                'levels_gained' => self::levelsGained($xp, $before),
            ];
        }

        $overall = [
            ...$this->describe('overall'),
            'xp' => (int) $now['exp'],
            'level' => is_array($now['skills']) ? $this->totalLevel($now['skills']) : null,
            'gained' => self::gain($now['exp'], $expBaseline['values']['exp']),
            'levels_gained' => is_array($now['skills']) ? (int) array_sum(array_column($skills, 'levels_gained')) : null,
        ];

        return [
            'period' => $period,
            'since' => $mappedFrom !== null && $mappedFrom > $this->history->periodStart($period) ? $mappedFrom : null,
            'skills' => [$overall, ...$skills],
            'bosses' => $this->counts(WomMetrics::BOSSES, $now['bosses'], fn (string $metric): mixed => $then('bosses', $metric), 'kills'),
            'activities' => $this->counts(WomMetrics::ACTIVITIES, $now['activities'], fn (string $metric): mixed => $then('activities', $metric), 'score'),
        ];
    }

    /**
     * Rows for a kill or score map; `$baseline` gives a metric's baseline value.
     *
     * @param  list<string>  $catalogue
     * @param  callable(string): mixed  $baseline
     * @return list<array<string, mixed>>
     */
    private function counts(array $catalogue, mixed $now, callable $baseline, string $valueKey): array
    {
        $rows = [];
        foreach ($this->ordered($catalogue, $now) as $metric => $value) {
            $rows[] = [
                ...$this->describe($metric),
                $valueKey => (int) $value,
                'gained' => self::gain($value, $baseline($metric)),
            ];
        }

        return $rows;
    }

    /**
     * Each account's newest map of `$field` (null when its newest snapshot has none).
     *
     * @param  list<int>  $accountIds
     * @return array<int, mixed> account id => map
     */
    private function latestMaps(array $accountIds, string $field): array
    {
        return array_map(fn (array $row): mixed => $row['values'][$field], $this->history->latest($accountIds, [$field]));
    }

    /**
     * A snapshot map in catalogue order, then any metric the catalogue does
     * not know yet (WOM adds bosses without notice), without `overall`.
     *
     * @param  list<string>  $catalogue
     * @return array<string, int|float>
     */
    private function ordered(array $catalogue, mixed $map): array
    {
        if (! is_array($map)) {
            return [];
        }

        unset($map['overall']);
        $rank = array_flip($catalogue);
        uksort($map, fn (string $a, string $b): int => ($rank[$a] ?? PHP_INT_MAX) <=> ($rank[$b] ?? PHP_INT_MAX));

        return $map;
    }

    /**
     * @return array{metric: string, label: string, icon: array<string, string>|null}
     */
    private function describe(string $metric): array
    {
        return ['metric' => $metric, 'label' => WomMetrics::label($metric), 'icon' => WomMetrics::icon($metric)];
    }

    /**
     * The sum of every skill's level; a skill missing from the map (unranked)
     * counts as level 1.
     *
     * @param  array<string, int|float>  $skills
     */
    private function totalLevel(array $skills): int
    {
        $metrics = array_diff(array_unique([...WomMetrics::SKILLS, ...array_keys($skills)]), ['overall']);

        return array_sum(array_map(
            fn (string $metric): int => Experience::levelFor((int) ($skills[$metric] ?? 0)),
            $metrics,
        ));
    }

    /**
     * A missing value can also read back as an empty array: an empty map is
     * stored as a BSON array, and a path into an array projects to one.
     */
    private static function gain(mixed $now, mixed $then): ?int
    {
        if (! is_numeric($now) || ! is_numeric($then)) {
            return null;
        }

        return max(0, (int) $now - (int) $then);
    }

    private static function levelsGained(mixed $xpNow, mixed $xpThen): ?int
    {
        if (! is_numeric($xpNow) || ! is_numeric($xpThen)) {
            return null;
        }

        return max(0, Experience::levelFor((int) $xpNow) - Experience::levelFor((int) $xpThen));
    }
}
