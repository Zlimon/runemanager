<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ChaosElemental extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_2h_sword',
        'kill_count',
        'obtained',
        'dragon_pickaxe',
        'pet_chaos_elemental',
    ];

    protected $hidden = ['user_id'];
}
