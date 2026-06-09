<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * SPEC §5.2 — the shared Group Ironman group bank. There is one group per
 * instance, so this is a singleton document overwritten on each push from any
 * member's plugin. Items are stored as a flat `[[itemId, quantity], …]` list
 * (the in-game group storage has no tabs).
 */
class GroupBank extends Model
{
    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    protected $guarded = [];
}
