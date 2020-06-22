<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GeneralGraardor extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'bandos_tassets',
        'bandos_chestplate',
        'bandos_hilt',
        'pet_general_graardor',
        'kill_count',
        'obtained',
        'godsword_shard_3',
        'bandos_boots',
        'godsword_shard_2',
        'godsword_shard_1',
    ];

    protected $hidden = ['user_id'];
}
