<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ChaosFanatic extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'kill_count',
        'obtained',
        'odium_shard_1',
        'malediction_shard_1',
        'pet_chaos_elemental',
    ];

    protected $hidden = ['user_id'];
}
