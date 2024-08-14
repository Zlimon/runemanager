<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ItemIcon extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'icons_items';

    protected $primaryKey = 'id';
}
