<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ThermonuclearSmokeDevil extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_smoke_devil',
        'occult_necklace',
        'smoke_battlestaff',
        'dragon_chainbody',
    ];

    protected $hidden = ['user_id'];
}
