<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Kraken extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'jar_of_dirt',
        'trident_of_the_seas_(full)',
        'kraken_tentacle',
        'kill_count',
        'obtained',
        'pet_kraken',
    ];

    protected $hidden = ['user_id'];
}
