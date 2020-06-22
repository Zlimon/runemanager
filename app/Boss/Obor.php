<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Obor extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'hill_giant_club',
        'kill_count',
        'obtained',
    ];

    protected $hidden = ['user_id'];
}
