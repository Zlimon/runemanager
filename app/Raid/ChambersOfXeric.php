<?php

namespace App\Raid;

use Illuminate\Database\Eloquent\Model;

class ChambersOfXeric extends Model
{
    protected $table = 'chambers_of_xeric';

    protected $fillable = [
        'obtained',
        'kill_count',
        'olmlet',
        'metamorphic_dust',
        'twisted_bow',
        'elder_maul',
        'kodai_insignia',
        'dragon_claws',
        'ancestral_hat',
        'ancestral_robe_top',
        'ancestral_robe_bottom',
        'dinhs_bulwark',
        'dexterous_prayer_scroll',
        'arcane_prayer_scroll',
        'dragon_hunter_crossbow',
        'twisted_buckler',
        'torn_prayer_scroll',
        'dark_relic',
        'onyx',
        'twisted_ancestral_colour_kit',
        'xerics_guard',
        'xerics_warrior',
        'xerics_sentinel',
        'xerics_general',
        'xerics_champion',
    ];

    protected $hidden = ['user_id'];
}
