<?php

namespace App\Services\WiseOldMan;

use App\Models\WomDailySnapshot;
use Illuminate\Support\Carbon;

/**
 * Reads over `WomDailySnapshot`, shared by the account, progress and
 * leaderboard views: daily points in a date range, and what a set of accounts
 * gained over a period. Dates are UTC `Y-m-d` strings, so they compare as
 * strings in Mongo.
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
     * Every snapshot of the given accounts on or after `$from`, oldest first,
     * streamed from a Mongo cursor so a long range never sits in memory whole.
     *
     * @param  list<int>  $accountIds
     * @return iterable<array{account_id: int, date: string, exp: int, ehp: float, ehb: float}>
     */
    public function points(array $accountIds, ?string $from): iterable
    {
        if ($accountIds === []) {
            return;
        }

        $match = ['account_id' => ['$in' => $accountIds]];
        if ($from !== null) {
            $match['date'] = ['$gte' => $from];
        }

        foreach ($this->cursor([
            ['$match' => $match],
            ['$sort' => ['date' => 1]],
            ['$project' => ['_id' => 0, 'account_id' => 1, 'date' => 1, 'exp' => 1, 'ehp' => 1, 'ehb' => 1]],
        ]) as $row) {
            yield $this->row($row);
        }
    }

    /**
     * Each account's first snapshot on or after `$date`, keyed by account id.
     *
     * @param  list<int>  $accountIds
     * @return array<int, array{account_id: int, date: string, exp: int, ehp: float, ehb: float}>
     */
    public function firstOnOrAfter(array $accountIds, ?string $date): array
    {
        return $this->firstPerAccount($accountIds, $date === null ? null : ['$gte' => $date], 1);
    }

    /**
     * Each account's last snapshot on or before `$date`, keyed by account id.
     *
     * @param  list<int>  $accountIds
     * @return array<int, array{account_id: int, date: string, exp: int, ehp: float, ehb: float}>
     */
    public function lastOnOrBefore(array $accountIds, string $date): array
    {
        return $this->firstPerAccount($accountIds, ['$lte' => $date], -1);
    }

    /**
     * What each account gained over a period: its latest snapshot minus its
     * last one on or before the period's start (or, for an account whose
     * history starts inside the period, its first one). Keyed by account id;
     * accounts without snapshots are left out.
     *
     * @param  list<int>  $accountIds
     * @return array<int, array{exp: int, ehp: float, ehb: float}>
     */
    public function gained(array $accountIds, string $period): array
    {
        if ($accountIds === []) {
            return [];
        }

        $start = $this->periodStart($period);
        $latest = $this->firstPerAccount($accountIds, null, -1);
        $baseline = $this->lastOnOrBefore($accountIds, $start)
            + $this->firstPerAccount($accountIds, ['$gt' => $start], 1);

        $gained = [];
        foreach ($latest as $accountId => $end) {
            $from = $baseline[$accountId] ?? $end;
            $gained[$accountId] = [
                'exp' => max(0, $end['exp'] - $from['exp']),
                'ehp' => max(0.0, round($end['ehp'] - $from['ehp'], 4)),
                'ehb' => max(0.0, round($end['ehb'] - $from['ehb'], 4)),
            ];
        }

        return $gained;
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
     * those matching `$dateMatch`.
     *
     * @param  list<int>  $accountIds
     * @param  array<string, string>|null  $dateMatch
     * @return array<int, array{account_id: int, date: string, exp: int, ehp: float, ehb: float}>
     */
    private function firstPerAccount(array $accountIds, ?array $dateMatch, int $direction): array
    {
        if ($accountIds === []) {
            return [];
        }

        $match = ['account_id' => ['$in' => $accountIds]];
        if ($dateMatch !== null) {
            $match['date'] = $dateMatch;
        }

        $rows = $this->aggregate([
            ['$match' => $match],
            ['$sort' => ['date' => $direction]],
            ['$group' => [
                '_id' => '$account_id',
                'account_id' => ['$first' => '$account_id'],
                'date' => ['$first' => '$date'],
                'exp' => ['$first' => '$exp'],
                'ehp' => ['$first' => '$ehp'],
                'ehb' => ['$first' => '$ehb'],
            ]],
        ]);

        $byAccount = [];
        foreach ($rows as $row) {
            $row = $this->row($row);
            $byAccount[$row['account_id']] = $row;
        }

        return $byAccount;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{account_id: int, date: string, exp: int, ehp: float, ehb: float}
     */
    private function row(array $row): array
    {
        return [
            'account_id' => (int) $row['account_id'],
            'date' => (string) $row['date'],
            'exp' => (int) ($row['exp'] ?? 0),
            'ehp' => (float) ($row['ehp'] ?? 0),
            'ehb' => (float) ($row['ehb'] ?? 0),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $pipeline
     * @return array<int, array<string, mixed>>
     */
    private function aggregate(array $pipeline): array
    {
        return iterator_to_array($this->cursor($pipeline), false);
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
