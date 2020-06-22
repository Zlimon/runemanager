<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GrotesqueGuardians extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'granite_hammer',
        'granite_dust',
        'noon',
        'jar_of_stone',
        'kill_count',
        'granite_ring',
        'obtained',
        'granite_gloves',
        'black_tourmaline_core',
    ];

    protected $hidden = ['user_id'];
}
