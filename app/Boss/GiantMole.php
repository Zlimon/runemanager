<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GiantMole extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'mole_skin',
        'kill_count',
        'mole_claw',
        'obtained',
        'baby_mole',
    ];

    protected $hidden = ['user_id'];
}
