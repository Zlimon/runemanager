<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Zulrah extends Model
{
    protected $table = 'zulrah';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_snakeling',
        'tanzanite_mutagen',
        'magma_mutagen',
        'jar_of_swamp',
        'magic_fang',
        'serpentine_visage',
        'tanzanite_fang',
        'zul-andra_teleport',
        'uncut_onyx',
        'zulrahs_scales',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
