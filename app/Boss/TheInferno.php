<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheInferno extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'infernal_cape',
        'jalnibrek',
        'kill_count',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
