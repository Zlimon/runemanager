<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Skotizo extends Model
{
    protected $table = 'skotizo';

    protected $fillable = [
        'obtained',
        'kill_count',
        'skotos',
        'jar_of_darkness',
        'dark_claw',
        'dark_totem',
        'uncut_onyx',
        'ancient_shard',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
