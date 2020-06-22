<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Zalcano extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'uncut_onyx',
        'crystal_tool_seed',
        'zalcano_shard',
        'kill_count',
        'obtained',
        'smolcano',
    ];

    protected $hidden = ['user_id'];
}
