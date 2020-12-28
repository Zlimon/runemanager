<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KrilTsutsaroth extends Model
{
    protected $table = 'kril_tsutsaroth';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kril_tsutsaroth',
        'staff_of_the_dead',
        'zamorakian_spear',
        'steam_battlestaff',
        'zamorak_hilt',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
