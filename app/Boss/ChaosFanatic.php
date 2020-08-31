<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ChaosFanatic extends Model
{
    protected $table = 'chaos_fanatic';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_chaos_elemental',
        'odium_shard_1',
        'malediction_shard_1',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
