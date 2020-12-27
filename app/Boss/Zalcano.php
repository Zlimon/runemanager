<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Zalcano extends Model
{
    protected $table = 'zalcano';

    protected $fillable = [
        'obtained',
        'kill_count',
        'smolcano',
        'crystal_tool_seed',
        'zalcano_shard',
        'uncut_onyx',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
