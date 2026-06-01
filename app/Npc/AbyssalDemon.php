<?php

namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class AbyssalDemon extends Model
{
    protected $table = 'abyssal_demon';

    protected $fillable = [
        'obtained',
        'kill_count',
        'abyssal_ashes',
        'air_rune',
        'chaos_rune',
        'blood_rune',
        'law_rune',
        'grimy_guam_leaf',
        'grimy_marrentill',
        'grimy_tarromin',
        'grimy_harralander',
        'grimy_ranarr_weed',
        'grimy_irit_leaf',
        'black_sword',
        'grimy_avantoe',
        'grimy_kwuarm',
        'grimy_cadantine',
        'grimy_lantadyme',
        'grimy_dwarf_weed',
        'pure_essence',
        'adamantite_bar',
        'coins',
        'steel_battleaxe',
        'lobster',
        'cosmic_talisman',
        'chaos_talisman',
        'defence_potion(3)',
        'black_axe',
        'mithril_kiteshield',
        'rune_chainbody',
        'ensouled_abyssal_head',
        'brimstone_key',
        'clue_scroll(hard)',
        'clue_scroll(elite)',
        'abyssal_head',
        'ancient_shard',
        'dark_totem_base',
        'dark_totem_middle',
        'rune_med_helm',
        'dark_totem_top',
        'abyssal_whip',
        'abyssal_dagger',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Models\Account::class);
    }
}