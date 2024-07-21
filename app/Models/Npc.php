<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Npc extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'monsters';
    protected $primaryKey = 'id';

    public function getDropItemsAttribute()
    {
        $itemIds = array_map(function ($drop) {
            return (string) $drop['id'];
        }, $this->drops);

        return Item::whereIn('id', $itemIds)->get();
    }
}
