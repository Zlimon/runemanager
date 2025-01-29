<?php

namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class DemonicGorilla extends Model
{
    protected $table = 'demonic_gorilla';

    protected $fillable = [
        'obtained',
        'kill_count',
        'zenyte_shard',
        'ballista_limbs',
        'ballista_springs',
        'light_frame',
        'heavy_frame',
        'monkey_tail',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}