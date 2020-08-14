<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Wintertodt extends Model
{
    protected $table = 'wintertodt';

    protected $fillable = [
        'obtained',
        'kill_count',
        'phoenix',
        'tome_of_fire_(empty)',
        'burnt_page',
        'pyromancer_garb',
        'pyromancer_hood',
        'pyromancer_robe',
        'pyromancer_boots',
        'warm_gloves',
        'bruma_torch',
        'dragon_axe',
    ];

    protected $hidden = ['user_id'];
}
