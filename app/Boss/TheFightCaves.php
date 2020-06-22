<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheFightCaves extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'tzrekjad',
        'fire_cape',
        'kill_count',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
