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
}
