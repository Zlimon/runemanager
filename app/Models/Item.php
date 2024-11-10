<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'mongodb-admin';

    protected $primaryKey = '_id';

    protected $keyType = 'int';

    protected $casts = [
        '_id' => 'int',
        'members' => 'bool',
        'stackable' => 'bool',
        'noted' => 'bool',
        'placeholder' => 'bool',
        'duplicate' => 'bool',
        'equipable' => 'bool',
        'tradeable' => 'bool',
        'noteable' => 'bool',
        'weight' => 'float',
        'highalch' => 'int',
        'lowalch' => 'int',
        'buy_limit' => 'int',
        'quest_item' => 'bool',
        'release_date' => 'date',
        'last_updated' => 'date',
    ];

    public static function randomItemId(): int
    {
        return (int) Item::where([['noted', false], ['placeholder', false], ['duplicate', false]])->whereNotNull('release_date')->get()->pluck('_id')->random();
    }
}
