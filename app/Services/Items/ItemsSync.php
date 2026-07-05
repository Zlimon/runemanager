<?php

namespace App\Services\Items;

use App\Models\Item;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;
use MongoDB\Collection;

/**
 * Backfill the static osrsbox item DB with items added to the game after the
 * osrsbox/osrsreboxed dataset was frozen (2024-11-27). Names and icons come
 * from the RuneLite static cache (updated with every game update); examine
 * text, alch values and GE metadata come from the OSRS Wiki price mapping
 * (tradeable items only). Existing documents are never touched.
 */
class ItemsSync
{
    protected const NAMES_URL = 'https://static.runelite.net/cache/item/names.json';

    protected const ICON_URL = 'https://static.runelite.net/cache/item/icon/%d.png';

    protected const WIKI_MAPPING_URL = 'https://prices.runescape.wiki/api/v1/osrs/mapping';

    protected const USER_AGENT = 'RuneManager item sync (https://github.com/Zlimon/runemanager)';

    protected HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->client = $client ?? new HttpClient;
    }

    /**
     * Insert every item id the game knows but the items collection lacks.
     *
     * @param  ?callable(string $itemId, string $name, bool $hasIcon): void  $progress
     * @return array{missing: int, inserted: int, icons_missing: int}
     */
    public function sync(?callable $progress = null, ?int $limit = null): array
    {
        $names = $this->currentItemNames();
        $missingIds = $this->missingItemIds($names);

        if ($limit !== null) {
            $missingIds = array_slice($missingIds, 0, $limit);
        }

        $result = ['missing' => count($missingIds), 'inserted' => 0, 'icons_missing' => 0];

        if ($missingIds === []) {
            return $result;
        }

        $mapping = $this->wikiMapping();
        $batch = [];

        foreach ($missingIds as $itemId) {
            $icon = $this->fetchIcon($itemId);

            if ($icon === null) {
                $result['icons_missing']++;
            }

            $batch[] = $this->buildDocument($itemId, $names[$itemId], $mapping[$itemId] ?? null, $icon);

            if ($progress !== null) {
                $progress((string) $itemId, $names[$itemId], $icon !== null);
            }

            if (count($batch) >= 100) {
                $this->itemsCollection()->insertMany($batch);
                $result['inserted'] += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->itemsCollection()->insertMany($batch);
            $result['inserted'] += count($batch);
        }

        return $result;
    }

    /**
     * The canonical id => name map for every item in the current game cache.
     *
     * @return array<int, string>
     */
    protected function currentItemNames(): array
    {
        $response = $this->client->request('GET', self::NAMES_URL, [
            'headers' => ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json'],
            'timeout' => 30,
        ]);

        $names = json_decode((string) $response->getBody(), true);

        if (! is_array($names) || $names === []) {
            throw new \RuntimeException('RuneLite names.json returned no items.');
        }

        $byId = [];

        foreach ($names as $id => $name) {
            if (is_string($name) && $name !== '' && strtolower($name) !== 'null') {
                $byId[(int) $id] = $name;
            }
        }

        ksort($byId);

        return $byId;
    }

    /**
     * The raw collection — Eloquent writes alias the `id` key to Mongo's `_id`,
     * which would strip the string OSRS id field every lookup matches on (see
     * {@see Item::lookupByOsrsIds}), so all writes here bypass the model.
     */
    protected function itemsCollection(): Collection
    {
        return (new Item)->getConnection()
            ->getDatabase()
            ->selectCollection((new Item)->getTable());
    }

    /**
     * @param  array<int, string>  $names
     * @return list<int>
     */
    protected function missingItemIds(array $names): array
    {
        $existing = $this->itemsCollection()->distinct('id');

        $existing = array_flip(array_map(strval(...), iterator_to_array($existing)));

        return array_values(array_filter(
            array_keys($names),
            fn (int $id): bool => ! isset($existing[(string) $id]),
        ));
    }

    /**
     * GE metadata (examine, members, alch values, buy limit) keyed by item id.
     * Best-effort: untradeable items aren't in the mapping, and an outage only
     * costs us metadata, not the item itself.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function wikiMapping(): array
    {
        try {
            $response = $this->client->request('GET', self::WIKI_MAPPING_URL, [
                'headers' => ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json'],
                'timeout' => 30,
            ]);

            $mapping = json_decode((string) $response->getBody(), true);

            if (! is_array($mapping)) {
                return [];
            }

            $byId = [];

            foreach ($mapping as $entry) {
                if (isset($entry['id'])) {
                    $byId[(int) $entry['id']] = $entry;
                }
            }

            return $byId;
        } catch (\Throwable $e) {
            Log::info('ItemsSync: wiki mapping fetch failed: '.$e->getMessage());

            return [];
        }
    }

    protected function fetchIcon(int $itemId): ?string
    {
        try {
            $response = $this->client->request('GET', sprintf(self::ICON_URL, $itemId), [
                'headers' => ['User-Agent' => self::USER_AGENT],
                'timeout' => 15,
            ]);

            $bytes = (string) $response->getBody();

            return $bytes === '' ? null : base64_encode($bytes);
        } catch (\Throwable $e) {
            Log::info('ItemsSync: icon fetch failed for '.$itemId.': '.$e->getMessage());

            return null;
        }
    }

    /**
     * A document in the osrsbox items shape (id stored as a string, icon as
     * base64 PNG) holding every field {@see Item::lookupByOsrsIds} projects,
     * plus the wiki metadata when the item is tradeable.
     *
     * @param  ?array<string, mixed>  $mapping
     * @return array<string, mixed>
     */
    protected function buildDocument(int $itemId, string $name, ?array $mapping, ?string $icon): array
    {
        return [
            'id' => (string) $itemId,
            'name' => $name,
            'examine' => $mapping['examine'] ?? null,
            'icon' => $icon,
            'members' => $mapping['members'] ?? null,
            'cost' => $mapping['value'] ?? null,
            'lowalch' => $mapping['lowalch'] ?? null,
            'highalch' => $mapping['highalch'] ?? null,
            'buy_limit' => $mapping['limit'] ?? null,
            'tradeable' => $mapping !== null,
            'tradeable_on_ge' => $mapping !== null,
            'noted' => false,
            'placeholder' => false,
            'duplicate' => false,
            'wiki_name' => $mapping !== null ? $name : null,
            'wiki_url' => $mapping !== null
                ? 'https://oldschool.runescape.wiki/w/'.str_replace(' ', '_', $name)
                : null,
        ];
    }
}
