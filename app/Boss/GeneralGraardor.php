<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GeneralGraardor extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_general_graardor',
        'bandos_chestplate',
        'bandos_tassets',
        'bandos_boots',
        'bandos_hilt',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];
}
