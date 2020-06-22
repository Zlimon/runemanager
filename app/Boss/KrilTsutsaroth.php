<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KrilTsutsaroth extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'zamorak_hilt',
        'staff_of_the_dead',
        'pet_kril_tsutsaroth',
        'kill_count',
        'obtained',
        'godsword_shard_3',
        'zamorakian_spear',
        'godsword_shard_2',
        'godsword_shard_1',
        'steam_battlestaff',
    ];

    protected $hidden = ['user_id'];
}
