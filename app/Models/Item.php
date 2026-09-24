<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Static item data from the osrsreboxed dataset (`docker/gamedata`). The OSRS
 * item id lives in the document's string `id` field, which the Mongo driver
 * aliases to `_id` in Eloquent queries — hence the raw aggregations below.
 */
#[Connection('mongodb-static')]
#[Table(key: '_id')]
class Item extends Model
{
    /**
     * A property rather than part of the Table attribute: the MongoDB base
     * model declares a string key type, which the attribute cannot override.
     */
    protected $keyType = 'int';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            '_id' => 'integer',
            'name' => 'string',
            'incomplete' => 'boolean',
            'members' => 'boolean',
            'tradeable' => 'boolean',
            'tradeable_on_ge' => 'boolean',
            'stackable' => 'boolean',
            'stacked' => 'boolean',
            'noted' => 'boolean',
            'noteable' => 'boolean',
            'linked_id_item' => 'integer',
            'linked_id_noted' => 'integer',
            'linked_id_placeholder' => 'integer',
            'placeholder' => 'boolean',
            'equipable' => 'boolean',
            'equipable_by_player' => 'boolean',
            'equipable_weapon' => 'boolean',
            'cost' => 'integer',
            'lowalch' => 'integer',
            'highalch' => 'integer',
            'weight' => 'integer',
            'buy_limit' => 'integer',
            'quest_item' => 'boolean',
            'release_date' => 'date',
            'duplicate' => 'boolean',
            'examine' => 'string',
            'icon' => 'string',
            'wiki_name' => 'string',
            'wiki_url' => 'string',
        ];
    }

    /**
     * The Mongo Laravel driver hardcodes an id↔_id alias in projections,
     * making the document's OSRS `id` field unreachable via Eloquent's
     * pluck/select. Drop to a raw $sample aggregation to bypass it.
     */
    public static function randomItemId(): int
    {
        $doc = (new static)->getConnection()
            ->getDatabase()
            ->selectCollection((new static)->getTable())
            ->aggregate([
                ['$match' => [
                    'noted' => false,
                    'placeholder' => false,
                    'duplicate' => false,
                    'release_date' => ['$ne' => null],
                ]],
                ['$sample' => ['size' => 1]],
                ['$project' => ['id' => 1, '_id' => 0]],
            ])
            ->toArray()[0] ?? null;

        return $doc ? (int) $doc['id'] : 0;
    }

    /**
     * A batch of random tradeable OSRS item ids in one $sample aggregation —
     * for seeding inventories/banks/loot without N round-trips.
     *
     * @return list<int>
     */
    public static function randomItemIds(int $count): array
    {
        if ($count < 1) {
            return [];
        }

        $docs = (new static)->getConnection()
            ->getDatabase()
            ->selectCollection((new static)->getTable())
            ->aggregate([
                ['$match' => [
                    'noted' => false,
                    'placeholder' => false,
                    'duplicate' => false,
                    'release_date' => ['$ne' => null],
                ]],
                ['$sample' => ['size' => $count]],
                ['$project' => ['id' => 1, '_id' => 0]],
            ])
            ->toArray();

        return array_values(array_map(fn ($doc): int => (int) ((array) $doc)['id'], $docs));
    }

    /**
     * Bulk lookup by OSRS item id (the document's `id` field, NOT Mongo's `_id`
     * ObjectId). The Laravel-Mongo driver auto-aliases id↔_id in WHERE clauses,
     * which makes Eloquent's where()/whereIn() against `id` silently match
     * nothing — so we drop to a raw aggregation here.
     *
     * Returns an associative array keyed by integer OSRS id so call sites can
     * do $items[$slotId] without a Collection wrapper. `_id` in the returned
     * shape is the OSRS id (also as int) — the existing Vue components index
     * tooltip state on `item._id`, so preserving that key keeps the contract.
     *
     * @param  list<int|string>  $osrsIds
     * @return array<int, array{_id: int, name: string, examine: ?string, icon: ?string, lowalch: ?int, highalch: ?int}>
     */
    public static function lookupByOsrsIds(array $osrsIds): array
    {
        if ($osrsIds === []) {
            return [];
        }

        // Documents store id as a string; coerce defensively.
        $needles = array_values(array_unique(array_map(
            fn ($id): string => (string) $id,
            $osrsIds,
        )));

        $docs = (new static)->getConnection()
            ->getDatabase()
            ->selectCollection((new static)->getTable())
            ->aggregate([
                ['$match' => ['id' => ['$in' => $needles]]],
                ['$project' => [
                    '_id' => 0,
                    'id' => 1,
                    'name' => 1,
                    'examine' => 1,
                    'icon' => 1,
                    'lowalch' => 1,
                    'highalch' => 1,
                ]],
            ])
            ->toArray();

        $out = [];
        foreach ($docs as $doc) {
            $arr = (array) $doc;
            $intId = (int) ($arr['id'] ?? 0);
            $out[$intId] = [
                '_id' => $intId,
                'name' => (string) ($arr['name'] ?? ''),
                'examine' => $arr['examine'] ?? null,
                'icon' => $arr['icon'] ?? null,
                'lowalch' => isset($arr['lowalch']) ? (int) $arr['lowalch'] : null,
                'highalch' => isset($arr['highalch']) ? (int) $arr['highalch'] : null,
            ];
        }

        return $out;
    }

    /**
     * Name search for the calendar's item picker. Matches a case-insensitive
     * substring against `name` and skips the noted/placeholder/duplicate
     * variants that would otherwise fill the list with the same item three
     * times. Like the lookups above this has to be a raw aggregation, because
     * the Mongo driver aliases `id` to `_id` in Eloquent queries.
     *
     * @return list<array{id: int, name: string, icon: string|null}>
     */
    public static function searchByName(string $term, int $limit = 20): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $docs = (new static)->getConnection()
            ->getDatabase()
            ->selectCollection((new static)->getTable())
            ->aggregate([
                ['$match' => [
                    'name' => ['$regex' => preg_quote($term, '/'), '$options' => 'i'],
                    'noted' => ['$ne' => true],
                    'placeholder' => ['$ne' => true],
                    'duplicate' => ['$ne' => true],
                ]],
                ['$project' => ['_id' => 0, 'id' => 1, 'name' => 1, 'icon' => 1]],
                ['$sort' => ['name' => 1]],
                ['$limit' => max(1, $limit)],
            ])
            ->toArray();

        return array_values(array_map(function ($doc): array {
            $arr = (array) $doc;

            return [
                'id' => (int) ($arr['id'] ?? 0),
                'name' => (string) ($arr['name'] ?? ''),
                'icon' => $arr['icon'] ?? null,
            ];
        }, $docs));
    }

    /**
     * Single-item form of {@see self::lookupByOsrsIds()}; an unknown id yields
     * the same shape with empty values rather than null.
     *
     * @return array{_id: int, name: string, examine: ?string, icon: ?string, lowalch: ?int, highalch: ?int}
     */
    public static function lookupByOsrsId(int|string $osrsId): array
    {
        $doc = (new static)->getConnection()
            ->getDatabase()
            ->selectCollection((new static)->getTable())
            ->aggregate([
                ['$match' => ['id' => (string) $osrsId]],
                ['$project' => [
                    '_id' => 0,
                    'id' => 1,
                    'name' => 1,
                    'examine' => 1,
                    'icon' => 1,
                    'lowalch' => 1,
                    'highalch' => 1,
                ]],
            ])
            ->toArray()[0] ?? null;

        $intId = (int) ($doc['id'] ?? 0);

        return [
            '_id' => $intId,
            'name' => (string) ($doc['name'] ?? ''),
            'examine' => $doc['examine'] ?? null,
            'icon' => $doc['icon'] ?? null,
            'lowalch' => isset($doc['lowalch']) ? (int) $doc['lowalch'] : null,
            'highalch' => isset($doc['highalch']) ? (int) $doc['highalch'] : null,
        ];
    }
}
