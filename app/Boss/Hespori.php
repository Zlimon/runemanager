<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Hespori extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'bottomless_compost_bucket',
        'iasor_seed',
        'kronos_seed',
        'attas_seed',
    ];

    protected $hidden = ['user_id'];
}
