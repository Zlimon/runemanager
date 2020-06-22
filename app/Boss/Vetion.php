<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Vetion extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'dragon_2h_sword',
        'ring_of_the_gods',
        'vetion_jr',
        'kill_count',
        'obtained',
        'dragon_pickaxe',
    ];

    protected $hidden = ['user_id'];
}
