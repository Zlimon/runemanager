<?php

namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class BlackKnight extends Model
{
    protected $table = 'black_knight';

    protected $fillable = [
        'obtained',
        'kill_count',
        'uncut_sapphire',
        'uncut_emerald',
        'uncut_ruby',
        'chaos_talisman',
        'nature_talisman',
        'uncut_diamond',
        'rune_javelin',
        'loop_half_of_key',
        'tooth_half_of_key',
        'rune_spear',
        'shield_left_half',
        'dragon_spear',
        'bones',
        'law_rune',
        'cosmic_rune',
        'mind_rune',
        'grimy_guam_leaf',
        'grimy_marrentill',
        'grimy_tarromin',
        'grimy_harralander',
        'grimy_ranarr_weed',
        'grimy_irit_leaf',
        'grimy_avantoe',
        'iron_sword',
        'grimy_kwuarm',
        'grimy_cadantine',
        'grimy_lantadyme',
        'grimy_dwarf_weed',
        'potato_seed',
        'onion_seed',
        'cabbage_seed',
        'tomato_seed',
        'sweetcorn_seed',
        'strawberry_seed',
        'iron_full_helm',
        'watermelon_seed',
        'snape_grass_seed',
        'steel_bar',
        'tin_ore',
        'pot_of_flour',
        'coins',
        'steel_mace',
        'bread',
        'mithril_arrow',
        'looting_bag',
        'body_rune',
        'chaos_rune',
        'earth_rune',
        'death_rune',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}