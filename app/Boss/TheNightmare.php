<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheNightmare extends Model
{
    protected $table = 'the_nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
        'little_nightmare',
        'inquisitors_mace',
        'inquisitors_great_helm',
        'inquisitors_hauberk',
        'inquisitors_plateskirt',
        'nightmare_staff',
        'volatile_orb',
        'harmonised_orb',
        'eldritch_orb',
        'jar_of_dreams',
    ];

    protected $hidden = ['user_id'];
}