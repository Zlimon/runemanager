<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheNightmare extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'little_nightmare',
        'inquisitors_hauberk',
        'inquisitors_plateskirt',
        'inquisitors_mace',
        'jar_of_dreams',
        'inquisitors_great_helm',
        'harmonised_orb',
        'nightmare_staff',
        'kill_count',
        'eldritch_orb',
        'obtained',
        'volatile_orb',
    ];

    protected $hidden = ['user_id'];
}
