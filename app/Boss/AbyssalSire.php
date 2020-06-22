<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class AbyssalSire extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'abyssal_orphan',
        'jar_of_miasma',
        'abyssal_whip',
        'unsired',
        'abyssal_head',
        'kill_count',
        'bludgeon_claw',
        'abyssal_dagger',
        'obtained',
        'bludgeon_spine',
        'bludgeon_axon',
    ];

    protected $hidden = ['user_id'];
}
