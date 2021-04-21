<?php

namespace App\Clues;

use Illuminate\Database\Eloquent\Model;

class AllTreasureTrails extends Model
{
    protected $table = 'all_treasure_trails';

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
