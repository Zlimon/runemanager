<?php

namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class IceWarrior extends Model
{
    protected $table = 'ice_warrior';

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
        'iron_battleaxe',
        'blood_rune',
        'grimy_guam_leaf',
        'grimy_marrentill',
        'grimy_tarromin',
        'grimy_harralander',
        'grimy_ranarr_weed',
        'grimy_irit_leaf',
        'grimy_avantoe',
        'grimy_kwuarm',
        'grimy_cadantine',
        'mithril_mace',
        'grimy_lantadyme',
        'grimy_dwarf_weed',
        'limpwurt_seed',
        'strawberry_seed',
        'marrentill_seed',
        'jangerberry_seed',
        'tarromin_seed',
        'wildblood_seed',
        'watermelon_seed',
        'harralander_seed',
        'nature_rune',
        'snape_grass_seed',
        'ranarr_seed',
        'whiteberry_seed',
        'mushroom_spore',
        'toadflax_seed',
        'belladonna_seed',
        'irit_seed',
        'poison_ivy_seed',
        'avantoe_seed',
        'cactus_seed',
        'chaos_rune',
        'kwuarm_seed',
        'potato_cactus_seed',
        'snapdragon_seed',
        'cadantine_seed',
        'lantadyme_seed',
        'dwarf_weed_seed',
        'torstol_seed',
        'coins',
        'law_rune',
        'cosmic_rune',
        'looting_bag',
        'clue_scroll(medium)',
        'larran's_key',
        'slayer's_enchantment',
        'mithril_arrow',
        'adamant_arrow',
        'death_rune',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}