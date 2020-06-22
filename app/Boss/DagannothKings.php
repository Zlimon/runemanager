<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DagannothKings extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_dagannoth_rex',
        'dragon_axe',
        'mud_battlestaff',
        'seers_ring',
        'berserker_ring',
        'kill_count',
        'pet_dagannoth_prime',
        'pet_dagannoth_supreme',
        'archers_ring',
        'seercull',
        'obtained',
        'warrior_ring',
    ];

    protected $hidden = ['user_id'];
}
