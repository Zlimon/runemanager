<?php

namespace App\Services\Plugin;

use App\Http\Middleware\LogPluginRequest;
use App\Models\PluginRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Laravel\Eloquent\Builder;

/**
 * Reads over the plugin request log (PluginRequest) for the admin traffic
 * monitor: totals, per-route and per-user breakdowns, the flagged feed and a
 * heuristic list of users (and anonymous IPs) worth a closer look. Every read
 * is bounded by one of the WINDOWS, and most take an optional user id to
 * scope it to one user's traffic.
 */
class PluginTraffic
{
    /** Window key => [length in minutes, timeline bucket in minutes]. */
    public const WINDOWS = [
        '1h' => [60, 5],
        '24h' => [1440, 60],
        '7d' => [10080, 360],
    ];

    public const DEFAULT_WINDOW = '24h';

    private const FLAGS_OR_EMPTY = ['$ifNull' => ['$flags', []]];

    /** Upper bounds (ms) of the latency histogram the p95 is read from. */
    public const LATENCY_BUCKETS_MS = [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000];

    /** A user needs this many requests before their error ratio counts. */
    public const MIN_REQUESTS_FOR_ERROR_RATE = 20;

    /** Share of a user's requests that failed with a 4xx before it is suspicious. */
    public const ERROR_RATE_THRESHOLD = 0.25;

    /** More account hashes than this behind one login is unusual (alts are few). */
    public const MAX_HASHES_PER_USER = 5;

    /** Unauthenticated requests from one IP, with no user, before it is listed. */
    public const MAX_ANONYMOUS_FAILURES = 10;

    /** The window key for a raw query value, falling back to the default. */
    public static function window(mixed $window): string
    {
        return is_string($window) && array_key_exists($window, self::WINDOWS) ? $window : self::DEFAULT_WINDOW;
    }

    public function requestCount(string $window): int
    {
        return $this->windowQuery($window)->count();
    }

    public function flaggedCount(string $window): int
    {
        return $this->windowQuery($window)->where('flags.0', 'exists', true)->count();
    }

    /**
     * Requests and errors per bucket across the window, oldest first, with
     * empty buckets filled in so the chart keeps a steady time axis.
     *
     * @return array<int, array{t: int, requests: int, errors: int}>
     */
    public function timeline(string $window, ?int $userId = null): array
    {
        [$minutes, $bucketMinutes] = self::WINDOWS[$window];
        $bucketMs = $bucketMinutes * 60_000;
        $time = ['$toLong' => '$created_at'];

        $rows = collect($this->aggregate([
            ['$match' => $this->match($window, $userId)],
            ['$group' => [
                '_id' => ['$subtract' => [$time, ['$mod' => [$time, $bucketMs]]]],
                'requests' => ['$sum' => 1],
                'errors' => ['$sum' => ['$cond' => [['$gte' => ['$status', 400]], 1, 0]]],
            ]],
        ]))->keyBy(fn (array $row): int => (int) $row['_id']);

        $end = intdiv(now()->getTimestampMs(), $bucketMs) * $bucketMs;
        $start = $end - ($minutes / $bucketMinutes - 1) * $bucketMs;

        $buckets = [];
        for ($t = $start; $t <= $end; $t += $bucketMs) {
            $buckets[] = [
                't' => $t,
                'requests' => (int) ($rows[$t]['requests'] ?? 0),
                'errors' => (int) ($rows[$t]['errors'] ?? 0),
            ];
        }

        return $buckets;
    }

    /**
     * @return array{requests: int, users: int, errors: int, error_rate: float, throttled: int, flagged: int, bytes: int, avg_ms: float}
     */
    public function summary(string $window, ?int $userId = null): array
    {
        return $this->overview($window, $userId)['summary'];
    }

    /**
     * @return array<int, array{route: string, requests: int, errors: int, error_rate: float, throttled: int, avg_ms: float, p95_ms: ?int, avg_bytes: int}>
     */
    public function routes(string $window, ?int $userId = null): array
    {
        return $this->overview($window, $userId)['routes'];
    }

    /**
     * @return array<int, array{user_id: int, name: ?string, requests: int, bytes: int, errors: int, error_rate: float, throttled: int, hashes: int}>
     */
    public function topUsers(string $window, int $limit = 10): array
    {
        return $this->overview($window, null, $limit)['topUsers'];
    }

    /**
     * The window's totals, its per-route breakdown and its busiest users, in
     * one aggregation pass ($facet over one $match).
     *
     * Routes carry volume, failure rate, mean latency and body size, busiest
     * first; a request that matched no named route is grouped under its path.
     * `p95_ms` is read off a latency histogram (LATENCY_BUCKETS_MS) built in
     * the same pass, so it is the upper bound of the bucket holding the 95th
     * percentile, or null past the last bucket. No $percentile (MongoDB 7+).
     *
     * @return array{summary: array<string, int|float>, routes: array<int, array<string, mixed>>, topUsers: array<int, array<string, mixed>>}
     */
    public function overview(string $window, ?int $userId = null, int $topUsers = 10): array
    {
        $errors = ['$sum' => ['$cond' => [['$gte' => ['$status', 400]], 1, 0]]];
        $throttled = ['$sum' => ['$cond' => [['$eq' => ['$status', 429]], 1, 0]]];
        $route = ['$ifNull' => ['$route', '$path']];

        $facets = $this->aggregate([
            ['$match' => $this->match($window, $userId)],
            ['$facet' => [
                'summary' => [
                    ['$group' => [
                        '_id' => null,
                        'requests' => ['$sum' => 1],
                        'users' => ['$addToSet' => '$user_id'],
                        'errors' => $errors,
                        'throttled' => $throttled,
                        'flagged' => ['$sum' => ['$cond' => [['$gt' => [['$size' => self::FLAGS_OR_EMPTY], 0]], 1, 0]]],
                        'bytes' => ['$sum' => '$request_bytes'],
                        'avg_ms' => ['$avg' => '$duration_ms'],
                    ]],
                ],
                'routes' => [
                    ['$group' => [
                        '_id' => $route,
                        'requests' => ['$sum' => 1],
                        'errors' => $errors,
                        'throttled' => $throttled,
                        'avg_ms' => ['$avg' => '$duration_ms'],
                        'avg_bytes' => ['$avg' => '$request_bytes'],
                    ]],
                    ['$sort' => ['requests' => -1, '_id' => 1]],
                ],
                'latency' => [
                    ['$group' => [
                        '_id' => ['route' => $route, 'bucket' => $this->latencyBucket()],
                        'count' => ['$sum' => 1],
                    ]],
                ],
                'topUsers' => [
                    ['$match' => ['user_id' => ['$ne' => null]]],
                    ['$group' => [
                        '_id' => '$user_id',
                        'requests' => ['$sum' => 1],
                        'bytes' => ['$sum' => '$request_bytes'],
                        'errors' => $errors,
                        'throttled' => $throttled,
                        'hashes' => ['$addToSet' => '$account_hash'],
                    ]],
                    ['$sort' => ['requests' => -1, '_id' => 1]],
                    ['$limit' => $topUsers],
                ],
            ]],
        ])[0];

        $summary = $facets['summary'][0] ?? [];
        $requests = (int) ($summary['requests'] ?? 0);

        $histograms = [];
        foreach ($facets['latency'] as $row) {
            $histograms[(string) $row['_id']['route']][(int) $row['_id']['bucket']] = (int) $row['count'];
        }

        $names = $this->userNames(array_column($facets['topUsers'], '_id'));

        return [
            'summary' => [
                'requests' => $requests,
                'users' => count(array_filter($summary['users'] ?? [], fn ($id) => $id !== null)),
                'errors' => (int) ($summary['errors'] ?? 0),
                'error_rate' => $requests > 0 ? round(($summary['errors'] ?? 0) / $requests, 3) : 0.0,
                'throttled' => (int) ($summary['throttled'] ?? 0),
                'flagged' => (int) ($summary['flagged'] ?? 0),
                'bytes' => (int) ($summary['bytes'] ?? 0),
                'avg_ms' => round((float) ($summary['avg_ms'] ?? 0), 1),
            ],
            'routes' => array_map(fn (array $row): array => [
                'route' => (string) $row['_id'],
                'requests' => (int) $row['requests'],
                'errors' => (int) $row['errors'],
                'error_rate' => round($row['errors'] / $row['requests'], 3),
                'throttled' => (int) $row['throttled'],
                'avg_ms' => round((float) $row['avg_ms'], 1),
                'p95_ms' => $this->percentileFromHistogram($histograms[(string) $row['_id']] ?? [], (int) $row['requests']),
                'avg_bytes' => (int) round((float) $row['avg_bytes']),
            ], $facets['routes']),
            'topUsers' => array_map(fn (array $row): array => [
                'user_id' => (int) $row['_id'],
                'name' => $names[$row['_id']] ?? null,
                'requests' => (int) $row['requests'],
                'bytes' => (int) $row['bytes'],
                'errors' => (int) $row['errors'],
                'error_rate' => round($row['errors'] / $row['requests'], 3),
                'throttled' => (int) $row['throttled'],
                'hashes' => count(array_filter($row['hashes'], fn ($hash) => $hash !== null)),
            ], $facets['topUsers']),
        ];
    }

    /**
     * Users (and anonymous IPs) whose traffic looks like abuse, each with the
     * reasons it was listed; most reasons first. A heuristic for a human to
     * review — nothing acts on it automatically.
     *
     * @return array<int, array{user_id: ?int, name: ?string, ip: ?string, reasons: array<int, string>}>
     */
    public function suspiciousUsers(string $window): array
    {
        $match = $this->match($window);
        $reasons = [];

        $users = $this->aggregate([
            ['$match' => $match + ['user_id' => ['$ne' => null]]],
            ['$group' => [
                '_id' => '$user_id',
                'requests' => ['$sum' => 1],
                // Failed logins are left out: they are attributed to the
                // address's owner, who may be the target, not the sender.
                'client_errors' => ['$sum' => ['$cond' => [['$and' => [
                    ['$gte' => ['$status', 400]], ['$lt' => ['$status', 500]], ['$ne' => ['$route', 'api.login']],
                ]], 1, 0]]],
                'throttled' => ['$sum' => ['$cond' => [['$in' => [LogPluginRequest::FLAG_THROTTLED, self::FLAGS_OR_EMPTY]], 1, 0]]],
                'conflicts' => ['$sum' => ['$cond' => [['$in' => [LogPluginRequest::FLAG_ACCOUNT_CONFLICT, self::FLAGS_OR_EMPTY]], 1, 0]]],
                'oversized' => ['$sum' => ['$cond' => [['$in' => [LogPluginRequest::FLAG_OVERSIZED, self::FLAGS_OR_EMPTY]], 1, 0]]],
                'hashes' => ['$addToSet' => '$account_hash'],
            ]],
        ]);

        foreach ($users as $row) {
            $id = (int) $row['_id'];
            $hashes = count(array_filter($row['hashes'], fn ($hash) => $hash !== null));
            $errorRate = $row['client_errors'] / $row['requests'];

            if ($row['requests'] >= self::MIN_REQUESTS_FOR_ERROR_RATE && $errorRate >= self::ERROR_RATE_THRESHOLD) {
                $reasons[$id][] = sprintf('%d%% of %d requests failed with a 4xx', round($errorRate * 100), $row['requests']);
            }
            if ($row['throttled'] > 0) {
                $reasons[$id][] = sprintf('Rate limited %d %s', $row['throttled'], $row['throttled'] === 1 ? 'time' : 'times');
            }
            if ($row['conflicts'] > 0) {
                $reasons[$id][] = sprintf('%d %s to push for an account linked to another user', $row['conflicts'], $row['conflicts'] === 1 ? 'attempt' : 'attempts');
            }
            if ($row['oversized'] > 0) {
                $reasons[$id][] = sprintf('%d oversized %s', $row['oversized'], $row['oversized'] === 1 ? 'request' : 'requests');
            }
            if ($hashes > self::MAX_HASHES_PER_USER) {
                $reasons[$id][] = sprintf('Sent %d different account hashes', $hashes);
            }
        }

        $sharedHashes = $this->aggregate([
            ['$match' => $match + ['user_id' => ['$ne' => null], 'account_hash' => ['$ne' => null]]],
            ['$group' => ['_id' => '$account_hash', 'users' => ['$addToSet' => '$user_id']]],
            ['$match' => ['users.1' => ['$exists' => true]]],
        ]);

        foreach ($sharedHashes as $row) {
            foreach ($row['users'] as $id) {
                $reasons[(int) $id][] = sprintf('Shares account hash %s with %d other %s', $this->shortHash($row['_id']), count($row['users']) - 1, count($row['users']) === 2 ? 'user' : 'users');
            }
        }

        $names = $this->userNames(array_keys($reasons));
        $suspicious = collect($reasons)->map(fn (array $list, int $id): array => [
            'user_id' => $id,
            'name' => $names[$id] ?? null,
            'ip' => null,
            'reasons' => array_values(array_unique($list)),
        ]);

        $anonymous = $this->aggregate([
            ['$match' => $match + ['user_id' => null, 'flags' => LogPluginRequest::FLAG_UNAUTHENTICATED]],
            ['$group' => ['_id' => '$ip', 'failures' => ['$sum' => 1], 'emails' => ['$addToSet' => '$login_email_hash']]],
            ['$match' => ['failures' => ['$gte' => self::MAX_ANONYMOUS_FAILURES]]],
        ]);

        foreach ($anonymous as $row) {
            $emails = count(array_filter($row['emails'], fn ($email) => $email !== null));
            $suspicious->push([
                'user_id' => null,
                'name' => null,
                'ip' => $row['_id'],
                'reasons' => array_values(array_filter([
                    sprintf('%d unauthenticated requests', $row['failures']),
                    $emails > 0 ? sprintf('Tried %d login %s', $emails, $emails === 1 ? 'email' : 'emails') : null,
                ])),
            ]);
        }

        return $suspicious->sortByDesc(fn (array $entry): int => count($entry['reasons']))->values()->all();
    }

    /**
     * Logged requests, newest first. Filters: `flag` (one of the logger's
     * flags), `user` (user id), `route` (route name) and `flagged` (only
     * requests that raised a flag).
     *
     * @param  array{flag?: ?string, user?: ?int, route?: ?string, flagged?: bool}  $filters
     */
    public function requests(string $window, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->windowQuery($window)->orderBy('created_at', 'desc');

        if ($filters['flagged'] ?? false) {
            $query->where('flags.0', 'exists', true);
        }
        if (! empty($filters['flag'])) {
            $query->where('flags', $filters['flag']);
        }
        if (! empty($filters['user'])) {
            $query->where('user_id', (int) $filters['user']);
        }
        if (! empty($filters['route'])) {
            // routes() keys a request that matched no named route by its path.
            $query->where(fn ($query) => $query->where('route', $filters['route'])
                ->orWhere(fn ($query) => $query->whereNull('route')->where('path', $filters['route'])));
        }

        $page = $query->paginate($perPage)->withQueryString();
        $names = $this->userNames($page->getCollection()->pluck('user_id')->filter()->unique()->all());

        return $page->through(fn (PluginRequest $request): array => [
            'id' => (string) $request->getKey(),
            'created_at' => $request->created_at?->toIso8601String(),
            'user_id' => $request->user_id,
            'user_name' => $names[$request->user_id] ?? null,
            'username' => $request->username,
            'account_hash' => $request->account_hash,
            'login_email' => $request->login_email,
            'unknown_login_email' => $request->login_email === null && $request->login_email_hash !== null,
            'route' => $request->route ?? $request->path,
            'method' => $request->method,
            'status' => $request->status,
            'duration_ms' => $request->duration_ms,
            'request_bytes' => $request->request_bytes,
            'ip' => $request->ip,
            'user_agent' => $request->user_agent,
            'flags' => $request->flags ?? [],
        ]);
    }

    /**
     * How often each flag was raised, most frequent first.
     *
     * @return array<string, int>
     */
    public function flagCounts(string $window, ?int $userId = null): array
    {
        return collect($this->aggregate([
            ['$match' => $this->match($window, $userId)],
            ['$unwind' => '$flags'],
            ['$group' => ['_id' => '$flags', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1, '_id' => 1]],
        ]))->mapWithKeys(fn (array $row): array => [(string) $row['_id'] => (int) $row['count']])->all();
    }

    /**
     * The account hashes a user pushed with, the usernames and account types
     * sent alongside each, and the IPs they came from.
     *
     * @return array{hashes: array<int, array{account_hash: string, usernames: array<int, string>, account_types: array<int, string>, requests: int, last_seen: ?string}>, ips: array<int, array{ip: string, requests: int, last_seen: ?string}>}
     */
    public function identities(string $window, int $userId): array
    {
        $match = $this->match($window, $userId);

        $hashes = $this->aggregate([
            ['$match' => $match + ['account_hash' => ['$ne' => null]]],
            ['$group' => [
                '_id' => '$account_hash',
                'usernames' => ['$addToSet' => '$username'],
                'account_types' => ['$addToSet' => '$account_type'],
                'requests' => ['$sum' => 1],
                'last_seen' => ['$max' => '$created_at'],
            ]],
            ['$sort' => ['requests' => -1]],
        ]);

        $ips = $this->aggregate([
            ['$match' => $match],
            ['$group' => ['_id' => '$ip', 'requests' => ['$sum' => 1], 'last_seen' => ['$max' => '$created_at']]],
            ['$sort' => ['requests' => -1]],
        ]);

        return [
            'hashes' => array_map(fn (array $row): array => [
                'account_hash' => (string) $row['_id'],
                'usernames' => array_values(array_filter($row['usernames'])),
                'account_types' => array_values(array_filter($row['account_types'])),
                'requests' => (int) $row['requests'],
                'last_seen' => $this->iso($row['last_seen']),
            ], $hashes),
            'ips' => array_map(fn (array $row): array => [
                'ip' => (string) $row['_id'],
                'requests' => (int) $row['requests'],
                'last_seen' => $this->iso($row['last_seen']),
            ], $ips),
        ];
    }

    /**
     * The index of the LATENCY_BUCKETS_MS bucket a request's duration falls
     * in (its duration is at most that bound), or the count of buckets for
     * one slower than the last.
     *
     * @return array<string, mixed>
     */
    private function latencyBucket(): array
    {
        $branches = [];
        foreach (self::LATENCY_BUCKETS_MS as $index => $bound) {
            $branches[] = ['case' => ['$lte' => ['$duration_ms', $bound]], 'then' => $index];
        }

        return ['$switch' => ['branches' => $branches, 'default' => count(self::LATENCY_BUCKETS_MS)]];
    }

    /**
     * The upper bound of the bucket holding the 95th percentile, or null when
     * it lies past the last bucket.
     *
     * @param  array<int, int>  $histogram  bucket index => requests
     */
    private function percentileFromHistogram(array $histogram, int $count): ?int
    {
        $rank = (int) ceil($count * 0.95);
        $seen = 0;

        foreach (self::LATENCY_BUCKETS_MS as $index => $bound) {
            $seen += $histogram[$index] ?? 0;

            if ($seen >= $rank) {
                return $bound;
            }
        }

        return null;
    }

    private function windowQuery(string $window): Builder
    {
        return PluginRequest::query()->where('created_at', '>=', $this->since($window));
    }

    /**
     * @return array<string, mixed>
     */
    private function match(string $window, ?int $userId = null): array
    {
        $match = ['created_at' => ['$gte' => new UTCDateTime($this->since($window))]];

        if ($userId !== null) {
            $match['user_id'] = $userId;
        }

        return $match;
    }

    private function since(string $window): Carbon
    {
        return now()->subMinutes(self::WINDOWS[$window][0]);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, string>
     */
    private function userNames(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return User::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    private function shortHash(mixed $hash): string
    {
        return mb_substr((string) $hash, 0, 8).'…';
    }

    private function iso(mixed $date): ?string
    {
        return $date instanceof UTCDateTime ? Carbon::instance($date->toDateTime())->toIso8601String() : null;
    }

    /**
     * Runs an aggregation against the request log, returning plain nested
     * PHP arrays rather than BSON documents.
     *
     * @param  array<int, array<string, mixed>>  $pipeline
     * @return array<int, array<string, mixed>>
     */
    private function aggregate(array $pipeline): array
    {
        $model = new PluginRequest;

        return $model->getConnection()
            ->getDatabase()
            ->selectCollection($model->getTable())
            ->aggregate($pipeline, [
                'allowDiskUse' => true,
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ])
            ->toArray();
    }
}
