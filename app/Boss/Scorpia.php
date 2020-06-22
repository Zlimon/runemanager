<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Scorpia extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'malediction_shard_3',
        'scorpias_offspring',
        'kill_count',
        'odium_shard_3',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
