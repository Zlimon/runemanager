<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Callisto extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'callisto_cub',
        'tyrannical_ring',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];
}
