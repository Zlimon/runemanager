<?php

namespace App\Services\Loot;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Item;
use App\Models\Loot;
use App\Services\Hiscores\LeaderboardPage;

/**
 * Loot hiscores aggregated from the plugin's loot log. A source's identity is
 * (source, type), not the name alone, so e.g. a pickpocketed "Man" and a
 * killed "Man" are distinct entries in different sections — matching how the
 * Loot Tracker itself records them.
 */
class LootLeaderboard
{
    /**
     * The Loot Tracker record types we receive, mapped to display sections and
     * ordered the way they appear on the loot directory.
     */
    public const CATEGORIES = [
        'NPC' => 'Monsters',
        'EVENT' => 'Events & Raids',
        'PICKPOCKET' => 'Pickpocketing',
        'PLAYER' => 'PvP',
    ];

    public const OTHER_CATEGORY = 'Other';

    public const OTHER_TOKEN = 'other';

    /**
     * Every unique loot source grouped into category sections by the
     * plugin-recorded type, sections in {@see CATEGORIES} order and sources
     * within a section ranked by combined value.
     *
     * @return array<int, array{label: string, sources: array<int, array{name: string, type: string, category: string, total_value: int, drops: int}>}>
     */
    public function sources(): array
    {
        $sources = collect($this->aggregate([
            ['$group' => [
                '_id' => ['source' => '$source', 'type' => '$type'],
                'total_value' => ['$sum' => '$total_value'],
                'drops' => ['$sum' => 1],
            ]],
            ['$sort' => ['total_value' => -1]],
        ]))->map(function (array $row): array {
            $type = $row['_id']['type'] ?? null;

            return [
                'name' => (string) $row['_id']['source'],
                'type' => self::tokenFor($type),
                'category' => self::categoryFor($type),
                'total_value' => (int) $row['total_value'],
                'drops' => (int) $row['drops'],
            ];
        });

        $order = [...array_values(self::CATEGORIES), self::OTHER_CATEGORY];

        return $sources
            ->groupBy('category')
            ->sortBy(fn ($_, string $category): int => array_search($category, $order, true))
            ->map(fn ($group, string $category): array => [
                'label' => $category,
                'sources' => $group->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Board for one (source, type) pair: every account with loot from it, each
     * with the items they've received (quantities summed across all of their
     * drops). Null when the source has no loot.
     *
     * The rows come back unranked and in no particular order — Mongo's `$sort`
     * has no tie-break of its own, so two accounts on the same total could
     * come back either way round on the next request. Ordering and ranking the
     * board is {@see LeaderboardPage::rankedBy()}'s job, as it is for every
     * other board built from our own data.
     *
     * @return ?array<int, array{account: array<string, mixed>, total_value: int, drops: int, items: array<int, array{id: int, quantity: int, name: ?string, icon: ?string}>}>
     */
    public function forSource(?string $type, string $source): ?array
    {
        $match = ['source' => $source, 'type' => $type];

        $totals = collect($this->aggregate([
            ['$match' => $match],
            ['$group' => [
                '_id' => '$account_id',
                'total_value' => ['$sum' => '$total_value'],
                'drops' => ['$sum' => 1],
            ]],
            ['$sort' => ['total_value' => -1]],
        ]))->map(fn (array $row): array => [
            'account_id' => (int) $row['_id'],
            'total_value' => (int) $row['total_value'],
            'drops' => (int) $row['drops'],
        ]);

        if ($totals->isEmpty()) {
            return null;
        }

        $itemsByAccount = $this->itemsByAccount($match);

        $itemDetails = Item::lookupByOsrsIds(
            collect($itemsByAccount)
                ->flatMap(fn (array $items): array => array_column($items, 'id'))
                ->unique()
                ->values()
                ->all(),
        );

        $accounts = Account::with('user')
            ->whereIn('id', $totals->pluck('account_id'))
            ->get()
            ->keyBy('id');

        return $totals
            ->filter(fn (array $total): bool => $accounts->has($total['account_id']))
            ->values()
            ->map(fn (array $total): array => [
                'account' => (new AccountResource($accounts[$total['account_id']]))->resolve(),
                'total_value' => $total['total_value'],
                'drops' => $total['drops'],
                'items' => array_map(fn (array $item): array => [
                    'id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'name' => $itemDetails[$item['id']]['name'] ?? null,
                    'icon' => $itemDetails[$item['id']]['icon'] ?? null,
                ], $itemsByAccount[$total['account_id']] ?? []),
            ])
            ->all();
    }

    /**
     * The stored form of a plugin-sent record type. RuneLite's LootRecordType
     * is uppercase (NPC, EVENT, PICKPOCKET…) and the boards match on it
     * exactly, so a client sending any other casing still lands on the same
     * record type rather than on a source page that finds nothing.
     */
    public static function recordType(?string $type): ?string
    {
        return filled($type) ? strtoupper($type) : null;
    }

    /**
     * Display section for a record type (null/unknown types fall under "Other").
     */
    public static function categoryFor(?string $type): string
    {
        return self::CATEGORIES[$type ?? ''] ?? self::OTHER_CATEGORY;
    }

    /**
     * URL-safe token for a record type, used in the per-source route.
     */
    public static function tokenFor(?string $type): string
    {
        return $type !== null ? strtolower($type) : self::OTHER_TOKEN;
    }

    /**
     * Resolves a route token back to the stored record type (null for "other",
     * which also matches legacy drops saved before types were recorded).
     */
    public static function typeForToken(string $token): ?string
    {
        return strtolower($token) === self::OTHER_TOKEN ? null : strtoupper($token);
    }

    /**
     * Summed item quantities per account for a (source, type) match, each
     * account's items ordered by quantity. Keyed by account id.
     *
     * @param  array<string, mixed>  $match
     * @return array<int, array<int, array{id: int, quantity: int}>>
     */
    private function itemsByAccount(array $match): array
    {
        $rows = $this->aggregate([
            ['$match' => $match],
            ['$unwind' => '$items'],
            ['$group' => [
                '_id' => '$account_id',
                // mongodb-laravel stores the embedded item id as `_id` (Eloquent
                // aliases it back to `id` on read); the raw driver sees `_id`.
                'items' => ['$push' => ['id' => '$items._id', 'quantity' => '$items.quantity']],
            ]],
        ]);

        $byAccount = [];
        foreach ($rows as $row) {
            $quantities = [];
            foreach ($row['items'] as $item) {
                $id = (int) $item['id'];
                $quantities[$id] = ($quantities[$id] ?? 0) + (int) $item['quantity'];
            }

            arsort($quantities);
            $byAccount[(int) $row['_id']] = array_map(
                fn (int $id, int $quantity): array => ['id' => $id, 'quantity' => $quantity],
                array_keys($quantities),
                array_values($quantities),
            );
        }

        return $byAccount;
    }

    /**
     * Runs an aggregation against the loot collection, returning plain nested
     * PHP arrays (rather than BSON documents) so callers can use array access
     * uniformly, including for embedded subdocuments.
     *
     * @param  array<int, array<string, mixed>>  $pipeline
     * @return array<int, array<string, mixed>>
     */
    private function aggregate(array $pipeline): array
    {
        return (new Loot)->getConnection()
            ->getDatabase()
            ->selectCollection((new Loot)->getTable())
            ->aggregate($pipeline, [
                'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ])
            ->toArray();
    }
}
