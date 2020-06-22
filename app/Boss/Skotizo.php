<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Skotizo extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'uncut_onyx',
        'skotos',
        'ancient_shard',
        'kill_count',
        'dark_totem',
        'obtained',
        'dark_claw',
        'jar_of_darkness',
    ];

    protected $hidden = ['user_id'];
}
