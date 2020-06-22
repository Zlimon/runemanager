<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Zulrah extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'magic_fang',
        'zulandra_teleport',
        'uncut_onyx',
        'tanzanite_mutagen',
        'kill_count',
        'pet_snakeling',
        'jar_of_swamp',
        'obtained',
        'tanzanite_fang',
        'magma_mutagen',
        'serpentine_visage',
        'zulrahs_scales',
    ];

    protected $hidden = ['user_id'];
}
