<?php

namespace App\Raid;

use Illuminate\Database\Eloquent\Model;

class TheatreOfBlood extends Model
{
    protected $table = 'theatre_of_blood';

    protected $fillable = [
        'obtained',
        'kill_count',
        'lil_zik',
        'scythhe_of_vitour_(uncharged)',
        'ghrazi_rapier',
        'sanguinesti_staff_(uncharged)',
        'justiciar_faceguard',
        'justiciar_chestguard',
        'justiciar_legguards',
        'avernic_defender_hilt',
        'vial_of_blood',
        'sinhaza_shroud_tier_1',
        'sinhaza_shroud_tier_2',
        'sinhaza_shroud_tier_3',
        'sinhaza_shroud_tier_4',
        'sinhaza_shroud_tier_5',
    ];

    protected $hidden = ['user_id'];
}
