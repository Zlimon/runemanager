<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KingBlackDragon extends Model
{
    protected $table = 'king_black_dragon';

    protected $fillable = [
        'obtained',
        'kill_count',
        'prince_black_dragon',
        'kbd_heads',
        'dragon_pickaxe',
        'draconic_visage',
    ];

    protected $hidden = ['user_id'];
}
