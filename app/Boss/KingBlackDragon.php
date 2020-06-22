<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KingBlackDragon extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'kbd_heads',
        'kill_count',
        'obtained',
        'prince_black_dragon',
        'dragon_pickaxe',
        'draconic_visage',
    ];

    protected $hidden = ['user_id'];
}
