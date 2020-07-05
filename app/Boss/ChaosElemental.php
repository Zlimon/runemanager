<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ChaosElemental extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_chaos_elemental',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];
}
