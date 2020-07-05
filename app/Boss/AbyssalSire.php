<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class AbyssalSire extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'abyssal_orphan',
        'unsired',
        'abyssal_head',
        'bludgeon_spine',
        'bludgeon_claw',
        'bludgeon_axon',
        'jar_of_miasma',
        'abyssal_dagger',
        'abyssal_whip',
    ];

    protected $hidden = ['user_id'];
}
