<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KalphiteQueen extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'kq_head',
        'dragon_2h_sword',
        'dragon_chainbody',
        'kalphite_princess',
        'kill_count',
        'jar_of_sand',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
