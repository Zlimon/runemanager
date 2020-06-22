<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ThermonuclearSmokeDevil extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_smoke_devil',
        'dragon_chainbody',
        'smoke_battlestaff',
        'kill_count',
        'obtained',
        'occult_necklace',
    ];

    protected $hidden = ['user_id'];
}
