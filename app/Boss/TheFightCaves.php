<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheFightCaves extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'tzrek-jad',
        'fire_cape',
    ];

    protected $hidden = ['user_id'];
}
