<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Wintertodt extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_axe',
        'burnt_page',
        'warm_gloves',
        'bruma_torch',
        'phoenix',
        'pyromancer_robe',
        'pyromancer_boots',
        'pyromancer_hood',
        'pyromancer_garb',
        'kill_count',
        'obtained',
        'tome_of_fire_(empty)',
    ];

    protected $hidden = ['user_id'];
}
