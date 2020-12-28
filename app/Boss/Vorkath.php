<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Vorkath extends Model
{
    protected $table = 'vorkath';

    protected $fillable = [
        'obtained',
        'kill_count',
        'vorki',
        'vorkaths_head',
        'draconic_visage',
        'skeletal_visage',
        'jar_of_decay',
        'dragonbone_necklace',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
