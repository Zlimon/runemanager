<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Callisto extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_2h_sword',
        'kill_count',
        'obtained',
        'dragon_pickaxe',
        'callisto_cub',
        'tyrannical_ring',
    ];

    protected $hidden = ['user_id'];
}
