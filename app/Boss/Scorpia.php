<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Scorpia extends Model
{
    protected $table = 'scorpia';

    protected $fillable = [
        'obtained',
        'kill_count',
        'scorpias_offspring',
        'odium_shard_3',
        'malediction_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
