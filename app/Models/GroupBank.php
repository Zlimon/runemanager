<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use MongoDB\Laravel\Eloquent\Model;

/**
 * The shared Group Ironman group bank. There is one group per
 * instance, so this is a singleton document overwritten on each push from any
 * member's plugin. Items are stored as a flat `[[itemId, quantity], …]` list
 * (the in-game group storage has no tabs).
 */
#[Connection('mongodb')]
#[Table(key: '_id')]
#[Unguarded]
class GroupBank extends Model
{
    //
}
