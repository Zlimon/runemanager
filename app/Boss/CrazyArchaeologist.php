<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CrazyArchaeologist extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'fedora',
        'odium_shard_2',
        'kill_count',
        'malediction_shard_2',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
