<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Monster extends Model
{
    protected $connection = 'mongodb-static';

    protected $primaryKey = '_id';

    protected $casts = [
        'name' => 'string',
        'members' => 'boolean',
        'release_date' => 'date',
        'duplicate' => 'boolean',
    ];
}
