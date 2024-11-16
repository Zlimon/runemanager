<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'mongodb-admin';

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

    public static function randomItemId(): int
    {
        return static::query()->where([['noted', false], ['placeholder', false], ['duplicate', false]])->whereNotNull('release_date')->pluck('_id')->random();
    }
}
