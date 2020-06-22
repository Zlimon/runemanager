<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class AlchemicalHydra extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_knife',
        'hydras_fang',
        'kill_count',
        'hydras_heart',
        'hydra_tail',
        'hydra_leather',
        'jar_of_chemicals',
        'hydras_claw',
        'dragon_thrownaxe',
        'hydras_eye',
        'ikkle_hydra',
        'obtained',
        'alchemical_hydra_heads',
    ];

    protected $hidden = ['user_id'];
}
