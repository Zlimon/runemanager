<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Vetion extends Model
{
    protected $table = 'vetion';

    protected $fillable = [
        'obtained',
        'kill_count',
        'vetion_jr',
        'ring_of_the_gods',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
