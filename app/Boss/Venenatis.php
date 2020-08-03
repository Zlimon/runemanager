<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Venenatis extends Model
{
    protected $table = 'venenatis';

    protected $fillable = [
        'obtained',
        'kill_count',
        'venenatis_spiderling',
        'treasonous_ring',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];
}
