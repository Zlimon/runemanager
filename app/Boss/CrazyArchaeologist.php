<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CrazyArchaeologist extends Model
{
    protected $table = 'crazy_archaeologist';

    protected $fillable = [
        'obtained',
        'kill_count',
        'odium_shard_2',
        'malediction_shard_2',
        'fedora',
    ];

    protected $hidden = ['user_id'];
}
