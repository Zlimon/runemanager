<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class AlchemicalHydra extends Model
{
    protected $table = 'alchemical_hydra';

    protected $fillable = [
        'obtained',
        'kill_count',
        'ikkle_hydra',
        'hydras_claw',
        'hydra_tail',
        'hydra_leather',
        'hydras_fang',
        'hydras_eye',
        'hydras_heart',
        'dragon_knife',
        'dragon_thrownaxe',
        'jar_of_chemicals',
        'alchemical_hydra_heads',
    ];

    protected $hidden = ['user_id'];
}
