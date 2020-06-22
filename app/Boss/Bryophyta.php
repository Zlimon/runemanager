<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Bryophyta extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'kill_count',
        'bryophytas_essence',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
