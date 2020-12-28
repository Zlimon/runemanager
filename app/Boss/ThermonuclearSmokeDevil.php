<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class ThermonuclearSmokeDevil extends Model
{
    protected $table = 'thermonuclear_smoke_devil';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_smoke_devil',
        'occult_necklace',
        'smoke_battlestaff',
        'dragon_chainbody',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
