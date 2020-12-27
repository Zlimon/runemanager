<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DagannothKings extends Model
{
    protected $table = 'dagannoth_kings';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_dagannoth_prime',
        'pet_dagannoth_supreme',
        'pet_dagannoth_rex',
        'berserker_ring',
        'archers_ring',
        'seers_ring',
        'warrior_ring',
        'dragon_axe',
        'seercull',
        'mud_battlestaff',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
