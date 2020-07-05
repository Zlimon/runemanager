<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheInferno extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'jal-nib-rek',
        'infernal_cape',
    ];

    protected $hidden = ['user_id'];
}
