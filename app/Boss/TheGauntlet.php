<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheGauntlet extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'youngllef',
        'crystal_armour_seed',
        'crystal_weapon_seed',
        'blade_of_saeldor_(inactive)',
        'gauntlet_cape',
    ];

    protected $hidden = ['user_id'];
}
