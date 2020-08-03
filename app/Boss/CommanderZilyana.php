<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CommanderZilyana extends Model
{
    protected $table = 'commander_zilyana';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_zilyana',
        'armadyl_crossbow',
        'saradomin_hilt',
        'saradomin_sword',
        'saradomins_light',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];
}
