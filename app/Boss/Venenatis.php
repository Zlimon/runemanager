<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Venenatis extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_2h_sword',
        'kill_count',
        'obtained',
        'dragon_pickaxe',
        'venenatis_spiderling',
        'treasonous_ring',
    ];

    protected $hidden = ['user_id'];
}
