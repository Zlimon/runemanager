<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheFightCaves extends Model
{
    protected $table = 'the_fight_caves';

    protected $fillable = [
        'obtained',
        'kill_count',
        'tzrek-jad',
        'fire_cape',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
