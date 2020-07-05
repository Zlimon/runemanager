<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Kraken extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kraken',
        'kraken_tentacle',
        'trident_of_the_seas_(full)',
        'jar_of_dirt',
    ];

    protected $hidden = ['user_id'];
}
