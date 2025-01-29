<?php

namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class AbyssalDemon extends Model
{
    protected $table = 'abyssal_demon';

    protected $fillable = [
        'obtained',
        'kill_count',
        'abyssal_whip',
        'abyssal_dagger',
        'abyssal_head',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}