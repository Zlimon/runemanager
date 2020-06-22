<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CommanderZilyana extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_zilyana',
        'saradomins_light',
        'kill_count',
        'obtained',
        'saradomin_hilt',
        'godsword_shard_3',
        'armadyl_crossbow',
        'godsword_shard_2',
        'saradomin_sword',
        'godsword_shard_1',
    ];

    protected $hidden = ['user_id'];
}
