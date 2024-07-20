<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\Item;

class Npc extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'monsters';
    protected $primaryKey = 'id';

    // Assuming drops is an array of objects with 'id' as one of the properties
    protected $casts = [
        'drops' => 'array',
    ];

    // Accessor to get the Item models for the drops
    public function getDropItemsAttribute()
    {
        // Extract item IDs from the drops array
        $itemIds = array_map(function ($drop) {
            return $drop['id'];
        }, $this->drops);

        return Item::whereIn('id', $itemIds)->get();
    }
}
