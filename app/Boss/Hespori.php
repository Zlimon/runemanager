<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Hespori extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'iasor_seed',
        'bottomless_compost_bucket',
        'attas_seed',
        'kill_count',
        'obtained',
        'kronos_seed',
    ];

    protected $hidden = ['user_id'];
}
