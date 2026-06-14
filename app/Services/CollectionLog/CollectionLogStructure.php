<?php

namespace App\Services\CollectionLog;

use App\Services\TempleOsrs\TempleOsrsClient;
use Illuminate\Support\Facades\Cache;

/**
 * The full TempleOSRS collection log structure — every category's complete,
 * ordered item-id list, grouped (bosses/raids/clues/minigames/other). Static
 * game data, so it's cached for a week; this is what lets the profile show
 * missing items and real per-category totals (the player sync only returns
 * obtained items).
 */
class CollectionLogStructure
{
    private const CACHE_KEY = 'templeosrs:collection-log-structure';

    public function __construct(private TempleOsrsClient $client) {}

    /**
     * @return array<string, array<string, list<int>>> group => slug => ids
     */
    public function groups(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addWeek(),
            fn (): array => $this->client->collectionLogStructure() ?? [],
        ) ?: [];
    }

    /**
     * Flattened slug => ordered item ids, plus slug => group, in one pass.
     *
     * @return array{ids: array<string, list<int>>, group: array<string, string>}
     */
    public function flatten(): array
    {
        $ids = [];
        $group = [];

        foreach ($this->groups() as $groupName => $categories) {
            foreach ($categories as $slug => $itemIds) {
                $ids[$slug] = array_map('intval', $itemIds);
                $group[$slug] = $groupName;
            }
        }

        return ['ids' => $ids, 'group' => $group];
    }
}
