<?php

namespace App\Clues;

use Illuminate\Database\Eloquent\Model;

class BeginnerTreasureTrails extends Model
{
    protected $table = 'beginner_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
        'mole_slippers',
        'frog_slippers',
        'bear_feet',
        'demon_feet',
        'jester_cape',
        'shoulder_parrot',
        'monks_robe_top_(t)',
        'monks_robe_(t)',
        'amulet_of_defence_(t)',
        'sandwich_lady_hat',
        'sandwich_lady_top',
        'sandwich_lady_bottom',
        'rune_scimitar_ornament_kit_(guthix)',
        'rune_scimitar_ornament_kit_(saradomin)',
        'rune_scimitar_ornament_kit_(zamorak)',
        'black_pickaxe',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
