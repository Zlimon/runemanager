<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Vorkath extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'vorkaths_head',
        'vorki',
        'kill_count',
        'skeletal_visage',
        'obtained',
        'jar_of_decay',
        'draconic_visage',
        'dragonbone_necklace',
    ];

    protected $hidden = ['user_id'];
}
