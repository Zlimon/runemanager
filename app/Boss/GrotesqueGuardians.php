<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GrotesqueGuardians extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'noon',
        'black_tourmaline_core',
        'granite_gloves',
        'granite_ring',
        'granite_hammer',
        'jar_of_stone',
        'granite_dust',
    ];

    protected $hidden = ['user_id'];
}
