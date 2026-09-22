<?php

namespace App\Services\CollectionLog;

use App\Models\Account;
use App\Models\Item;

/**
 * The account profile's collection log panel, built from the stored TempleOSRS
 * data. The player sync only returns *obtained* items per category, so they are
 * overlaid on the full TempleOSRS structure (every category's complete item
 * list, grouped) to show missing items and real per-category totals, mirroring
 * the TempleOSRS UI. Only the active category's items are hydrated, to keep the
 * payload small.
 */
class CollectionLogView
{
    public function __construct(private CollectionLogStructure $structure) {}

    /**
     * Build the panel for an account, or null when it has no synced log. An
     * unknown or missing $activeSlug falls back to the first category.
     *
     * When the structure can't be fetched the obtained-only set is used instead,
     * so the panel still works offline (without missing items or real totals).
     *
     * @return array{
     *     categories: list<array{slug: string, name: string, group: string, obtained: int, total: int}>,
     *     activeSlug: ?string,
     *     activeCollection: ?array<string, mixed>,
     *     obtained: int,
     *     total: int,
     *     fetchedAt: ?string,
     * }|null
     */
    public function build(Account $account, ?string $activeSlug): ?array
    {
        $log = $account->collectionLog;
        if ($log === null) {
            return null;
        }

        $obtainedItems = $log->items ?? [];
        $flat = $this->structure->flatten();
        $catIds = $flat['ids'] ?: array_map(
            fn (array $entries): array => array_map(fn (array $e): int => (int) $e['id'], $entries),
            $obtainedItems,
        );

        $categories = [];
        foreach ($catIds as $slug => $ids) {
            $obtainedCount = count(array_intersect($ids, array_column($obtainedItems[$slug] ?? [], 'id')));
            $categories[$slug] = [
                'slug' => $slug,
                'name' => $this->prettyCategoryName($slug),
                'group' => $flat['group'][$slug] ?? 'other',
                'obtained' => $obtainedCount,
                'total' => count($ids),
            ];
        }
        uasort($categories, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        $activeSlug = ($activeSlug && isset($categories[$activeSlug]))
            ? $activeSlug
            : (array_key_first($categories) ?: null);

        $activeCollection = $activeSlug !== null
            ? $this->loadCollectionItems($categories[$activeSlug], $catIds[$activeSlug], $obtainedItems[$activeSlug] ?? [])
            : null;

        return [
            'categories' => array_values($categories),
            'activeSlug' => $activeSlug,
            'activeCollection' => $activeCollection,
            'obtained' => (int) $log->obtained,
            'total' => (int) $log->total,
            'fetchedAt' => $log->fetched_at?->toIso8601String(),
        ];
    }

    /**
     * Hydrate one category's full ordered item list with icon/name/examine, marking
     * which the player has obtained (with quantity + date) and which are missing.
     * Items store the OSRS id in the `id` field (Mongo `_id` is an ObjectId), so
     * this reuses the shared lookup the inventory/bank/loot panels use.
     *
     * @param  array{slug: string, name: string, group: string, obtained: int, total: int}  $summary
     * @param  list<int>  $orderedIds  the category's complete item list, in display order
     * @param  array<int, array<string, mixed>>  $obtained  the player's obtained entries
     * @return array{slug: string, name: string, group: string, obtained: int, total: int, items: list<array{id: int, item: mixed, quantity: int, date: ?string, obtained: bool}>}
     */
    private function loadCollectionItems(array $summary, array $orderedIds, array $obtained): array
    {
        $details = Item::lookupByOsrsIds($orderedIds);
        $obtainedById = collect($obtained)->keyBy(fn (array $e): int => (int) $e['id']);

        $slots = array_map(function (int $id) use ($details, $obtainedById): array {
            $entry = $obtainedById->get($id);

            return [
                'id' => $id,
                'item' => $details[$id] ?? null,
                'quantity' => (int) ($entry['count'] ?? 0),
                'date' => $entry['date'] ?? null,
                'obtained' => $entry !== null,
            ];
        }, $orderedIds);

        return [
            'slug' => $summary['slug'],
            'name' => $summary['name'],
            'group' => $summary['group'],
            'obtained' => $summary['obtained'],
            'total' => $summary['total'],
            'items' => $slots,
        ];
    }

    private function prettyCategoryName(string $slug): string
    {
        return ucwords(str_replace('_', ' ', $slug));
    }
}
