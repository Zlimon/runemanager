<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Bryophyta extends Model
{
    protected $table = 'bryophyta';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bryophytas_essence',
    ];

    protected $hidden = ['user_id'];
}
