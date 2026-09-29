<?php

namespace App\Services\WiseOldMan;

use App\Models\WomDailySnapshot;
use Illuminate\Support\Carbon;

/**
 * Reads over `WomDailySnapshot`, shared by the gains, progress and leaderboard
 * views: each account's latest snapshot, its baseline for a period, and one
 * field's daily values over a range. Fields are document paths
 * (`exp`, `skills`, `bosses.zulrah`, …; see {@see WomMetrics::path()}).
 * Dates are UTC `Y-m-d` strings, so they compare as strings in Mongo.
 */
class WomSnapshotHistory
{
    /** Chart ranges => days back from today; `all` has no start. */
    public const RANGES = ['week' => 7, 'month' => 30, 'year' => 365, 'all' => null];

    /** Gain periods => days back from today. */
    public const PERIODS = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];

    /** A chart line never carries more points than this. */
    public const MAX_POINTS = 400;

    /** The first date a range covers, or null for `all`. */
    public function rangeStart(string $range): ?string
    {
        // Not `??`: `all` maps to null on purpose.
        $days = array_key_exists($range, self::RANGES) ? self::RANGES[$range] : self::RANGES['year'];

        return $days === null ? null : Carbon::now('UTC')->subDays($days)->toDateString();
    }

    /** The date a period's gains are measured from. */
    public function periodStart(string $period): string
    {
        return Carbon::now('UTC')->subDays(self::PERIODS[$period] ?? self::PERIODS['week'])->toDateString();
    }

    /**
     * Each account's newest snapshot, keyed by account id. A path the newest
     * snapshot lacks is null.
     *
     * @param  list<int>  $accountIds
     * @param  list<string>  $paths
     * @return array<int, array{account_id: int, date: string, values: array<string, mixed>}>
     */
    public function latest(array $accountIds, array $paths): array
    {
        return $this->firstPerAccount($accountIds, null, -1, $paths);
    }

    /**
     * Each account's snapshot a period's gains are measured from: its last one
     * on or before the period start or, for an account whose history starts
     * inside the period, its first one. Keyed by account id. Suits `exp`,
     * which every snapshot carries; map metrics use {@see self::metricBaselines()}.
     *
     * @param  list<int>  $accountIds
     * @param  list<string>  $paths
     * @return array<int, array{account_id: int, date: string, values: array<string, mixed>}>
     */
    public function baseline(array $accountIds, string $period, array $paths): array
    {
        $start = $this->periodStart($period);

        return $this->firstPerAccount($accountIds, ['$lte' => $start], -1, $paths)
            + $this->firstPerAccount($accountIds, ['$gt' => $start], 1, $paths);
    }

    /**
     * Where each metric of one map (`skills`, `bosses`, `activities`) is
     * measured from, per account: its value in the last snapshot on or before
     * the period start that carries the map, or, when that snapshot lacks it
     * (unranked then, or no mapped snapshot that old), its first appearance
     * after the start. Headline-only snapshots written before the maps were
     * stored are skipped. Carries each baseline's date, for one account's
     * Gains tab; population boards use {@see self::metricBaselineValues()}.
     *
     * @param  list<int>  $accountIds
     * @return array<int, array<string, array{date: string, value: int|float}>> account id => metric => baseline
     */
    public function metricBaselines(array $accountIds, string $period, string $field): array
    {
        if ($accountIds === []) {
            return [];
        }

        $start = $this->periodStart($period);
        $baselines = [];

        // $min over {date, value} compares the date first: the earliest appearance.
        foreach ($this->cursor([
            ['$match' => ['account_id' => ['$in' => $accountIds], 'date' => ['$gt' => $start], $field => ['$type' => 'object']]],
            ['$project' => ['account_id' => 1, 'date' => 1, 'entry' => ['$objectToArray' => '$'.$field]]],
            ['$unwind' => '$entry'],
            ['$group' => [
                '_id' => ['account_id' => '$account_id', 'metric' => '$entry.k'],
                'first' => ['$min' => ['date' => '$date', 'value' => '$entry.v']],
            ]],
        ]) as $row) {
            $baselines[(int) $row['_id']['account_id']][$row['_id']['metric']] = [
                'date' => (string) $row['first']['date'],
                'value' => $row['first']['value'],
            ];
        }

        foreach ($this->firstPerAccount($accountIds, ['$lte' => $start], -1, [$field], $field) as $accountId => $row) {
            foreach ((array) $row['values'][$field] as $metric => $value) {
                $baselines[$accountId][$metric] = ['date' => $row['date'], 'value' => $value];
            }
        }

        return $baselines;
    }

    /**
     * The baseline values of {@see self::metricBaselines()} without their
     * dates, for a whole population: `$latestMaps` is each account's newest
     * map of `$field`. Only accounts whose newest map has a metric their last
     * mapped snapshot on or before the start lacks are looked up forward, and
     * that lookup merges each account's maps newest first, so every metric
     * ends on its first value after the start.
     *
     * @param  array<int, mixed>  $latestMaps  account id => newest map
     * @return array<int, array<string, int|float>> account id => metric => baseline value
     */
    public function metricBaselineValues(array $latestMaps, string $period, string $field): array
    {
        if ($latestMaps === []) {
            return [];
        }

        $start = $this->periodStart($period);
        $before = $this->firstPerAccount(array_keys($latestMaps), ['$lte' => $start], -1, [$field], $field);

        $baselines = [];
        $lookForward = [];
        foreach ($latestMaps as $accountId => $map) {
            $known = $before[$accountId]['values'][$field] ?? [];
            $baselines[$accountId] = is_array($known) ? $known : [];

            if (array_diff_key((array) $map, $baselines[$accountId]) !== []) {
                $lookForward[] = $accountId;
            }
        }

        if ($lookForward === []) {
            return $baselines;
        }

        foreach ($this->cursor([
            ['$match' => ['account_id' => ['$in' => $lookForward], 'date' => ['$gt' => $start], $field => ['$type' => 'object']]],
            ['$sort' => ['account_id' => -1, 'date' => -1]],
            ['$group' => ['_id' => '$account_id', 'first' => ['$mergeObjects' => '$'.$field]]],
        ]) as $row) {
            $accountId = (int) $row['_id'];
            $baselines[$accountId] += $row['first'];
        }

        return $baselines;
    }

    /**
     * {@see self::metricBaselines()} for one map metric path (`bosses.zulrah`).
     *
     * @param  list<int>  $accountIds
     * @return array<int, array{date: string, value: int|float}> account id => baseline
     */
    public function pathBaselines(array $accountIds, string $period, string $path): array
    {
        if ($accountIds === []) {
            return [];
        }

        $start = $this->periodStart($period);
        $field = explode('.', $path)[0];
        $baselines = [];

        foreach ($this->firstPerAccount($accountIds, ['$lte' => $start], -1, [$path], $field) as $accountId => $row) {
            if (is_numeric($row['values'][$path])) {
                $baselines[$accountId] = ['date' => $row['date'], 'value' => $row['values'][$path]];
            }
        }

        $lookForward = array_values(array_diff($accountIds, array_keys($baselines)));
        if ($lookForward === []) {
            return $baselines;
        }

        // $min over {date, value} compares the date first: the earliest appearance.
        foreach ($this->cursor([
            ['$match' => ['account_id' => ['$in' => $lookForward], 'date' => ['$gt' => $start], $path => ['$exists' => true]]],
            ['$group' => ['_id' => '$account_id', 'first' => ['$min' => ['date' => '$date', 'value' => '$'.$path]]]],
        ]) as $row) {
            $baselines[(int) $row['_id']] = ['date' => (string) $row['first']['date'], 'value' => $row['first']['value']];
        }

        return $baselines;
    }

    /**
     * One account's daily values of a numeric path from `$from` on, oldest
     * first, skipping days whose snapshot lacks the path.
     *
     * @return list<array{date: string, value: int|float}>
     */
    public function values(int $accountId, string $path, ?string $from): array
    {
        $match = ['account_id' => $accountId, $path => ['$exists' => true, '$ne' => null]];
        if ($from !== null) {
            $match['date'] = ['$gte' => $from];
        }

        $values = [];
        foreach ($this->cursor([
            ['$match' => $match],
            ['$sort' => ['date' => 1]],
            ['$project' => ['_id' => 0, 'date' => 1, 'value' => '$'.$path]],
        ]) as $row) {
            $values[] = ['date' => (string) $row['date'], 'value' => $row['value']];
        }

        return $values;
    }

    /**
     * Thin a series to at most {@see self::MAX_POINTS}, keeping the first and
     * last points so the line still spans the whole range.
     *
     * @template T
     *
     * @param  list<T>  $points
     * @return list<T>
     */
    public function downsample(array $points): array
    {
        $count = count($points);
        if ($count <= self::MAX_POINTS) {
            return $points;
        }

        $step = (int) ceil(($count - 1) / (self::MAX_POINTS - 1));
        $kept = [];
        for ($i = 0; $i < $count - 1; $i += $step) {
            $kept[] = $points[$i];
        }
        $kept[] = $points[$count - 1];

        return $kept;
    }

    /** A snapshot date as the chart's millisecond timestamp (UTC midnight). */
    public function timestamp(string $date): int
    {
        return Carbon::parse($date, 'UTC')->startOfDay()->getTimestampMs();
    }

    /**
     * One snapshot per account: the first in `$direction` date order among
     * those matching `$dateMatch` (and carrying `$requiring`), projected to `$paths`.
     *
     * @param  list<int>  $accountIds
     * @param  array<string, string>|null  $dateMatch
     * @param  list<string>  $paths
     * @return array<int, array{account_id: int, date: string, values: array<string, mixed>}>
     */
    private function firstPerAccount(array $accountIds, ?array $dateMatch, int $direction, array $paths, ?string $requiring = null): array
    {
        if ($accountIds === []) {
            return [];
        }

        $match = ['account_id' => ['$in' => $accountIds]];
        if ($dateMatch !== null) {
            $match['date'] = $dateMatch;
        }
        if ($requiring !== null) {
            $match[$requiring] = ['$exists' => true];
        }

        // $group output names cannot contain dots, so paths travel as f0, f1, …
        $group = ['_id' => '$account_id', 'date' => ['$first' => '$date']];
        foreach (array_values($paths) as $index => $path) {
            $group["f{$index}"] = ['$first' => '$'.$path];
        }

        // Sorting on both keys of the (account_id, date) index lets Mongo walk
        // the index to each account's first doc instead of sorting everything.
        $byAccount = [];
        foreach ($this->cursor([
            ['$match' => $match],
            ['$sort' => ['account_id' => $direction, 'date' => $direction]],
            ['$group' => $group],
        ]) as $row) {
            $values = [];
            foreach (array_values($paths) as $index => $path) {
                $values[$path] = $row["f{$index}"] ?? null;
            }

            $byAccount[(int) $row['_id']] = [
                'account_id' => (int) $row['_id'],
                'date' => (string) $row['date'],
                'values' => $values,
            ];
        }

        return $byAccount;
    }

    /**
     * Runs an aggregation against the snapshot collection, yielding plain
     * PHP arrays one document at a time.
     *
     * @param  array<int, array<string, mixed>>  $pipeline
     * @return \Traversable<int, array<string, mixed>>
     */
    private function cursor(array $pipeline): \Traversable
    {
        $model = new WomDailySnapshot;

        return $model->getConnection()
            ->getDatabase()
            ->selectCollection($model->getTable())
            ->aggregate($pipeline, [
                'allowDiskUse' => true,
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]);
    }
}
