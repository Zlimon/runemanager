<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'mongodb-static';

    protected $primaryKey = '_id';

    protected $keyType = 'int'; // _id is item id

    protected $casts = [
        '_id' => 'integer',
        'name' => 'string',
        //        'last_updated' => 'date',
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
        //        'equipment' => 'array',
        //        'weapon' => 'array',
    ];

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
}
