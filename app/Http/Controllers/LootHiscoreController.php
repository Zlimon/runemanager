<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Item;
use App\Models\Loot;
use Inertia\Inertia;
use Inertia\Response;

class LootHiscoreController extends Controller
{
    /**
     * The Loot Tracker record types we receive, mapped to display sections and
     * ordered the way they appear on the loot directory (SPEC §7 loot-by-source).
     */
    private const CATEGORIES = [
        'NPC' => 'Monsters',
        'EVENT' => 'Events & Raids',
        'PICKPOCKET' => 'Pickpocketing',
        'PLAYER' => 'PvP',
    ];

    private const OTHER_CATEGORY = 'Other';

    private const OTHER_TOKEN = 'other';

    /**
     * SPEC §7 — loot directory. Lists every unique loot source grouped into
     * category sections by the plugin-recorded type. A source's identity is
     * (source, type), not the name alone, so e.g. a pickpocketed "Man" and a
     * killed "Man" are distinct entries in different sections — matching how
     * the Loot Tracker itself records them. Drilling in hits {@see show()}.
     */
    public function index(): Response
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
                'type' => $this->tokenFor($type),
                'category' => $this->categoryFor($type),
                'total_value' => (int) $row['total_value'],
                'drops' => (int) $row['drops'],
            ];
        });

        $order = [...array_values(self::CATEGORIES), self::OTHER_CATEGORY];

        $groups = $sources
            ->groupBy('category')
            ->sortBy(fn ($_, string $category): int => array_search($category, $order, true))
            ->map(fn ($group, string $category): array => [
                'label' => $category,
                'sources' => $group->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Hiscores/Loot/Index', [
            'recordType' => 'loot',
            'groups' => $groups,
        ]);
    }

    /**
     * SPEC §7 — per-source board for one (source, type) pair. Shows every
     * account with loot from it, ranked by total value, each with the grid of
     * items they've received (quantities summed across all of their drops).
     */
    public function show(string $type, string $source): Response
    {
        $recordType = $this->typeForToken($type);
        $match = ['source' => $source, 'type' => $recordType];

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

        abort_if($totals->isEmpty(), 404);

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

        $hiscores = $totals
            ->filter(fn (array $total): bool => $accounts->has($total['account_id']))
            ->values()
            ->map(fn (array $total, int $index): array => [
                'rank' => $index + 1,
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

        return Inertia::render('Hiscores/Loot/Show', [
            'recordType' => 'loot',
            'source' => $source,
            'category' => $this->categoryFor($recordType),
            'hiscores' => $hiscores,
        ]);
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
     * Display section for a record type (null/unknown types fall under "Other").
     */
    private function categoryFor(?string $type): string
    {
        return self::CATEGORIES[$type ?? ''] ?? self::OTHER_CATEGORY;
    }

    /**
     * URL-safe token for a record type, used in the per-source route.
     */
    private function tokenFor(?string $type): string
    {
        return $type !== null ? strtolower($type) : self::OTHER_TOKEN;
    }

    /**
     * Resolves a route token back to the stored record type (null for "other",
     * which also matches legacy drops saved before types were recorded).
     */
    private function typeForToken(string $token): ?string
    {
        return strtolower($token) === self::OTHER_TOKEN ? null : strtoupper($token);
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
