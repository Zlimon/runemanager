<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class KalphiteQueen extends Model
{
    protected $table = 'kalphite_queen';

    protected $fillable = [
        'obtained',
        'kill_count',
        'kalphite_princess',
        'kq_head',
        'jar_of_sand',
        'dragon_2h_sword',
        'dragon_chainbody',
    ];

    protected $hidden = ['user_id'];
}
