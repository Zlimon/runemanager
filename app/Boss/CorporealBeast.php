<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CorporealBeast extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'holy_elixir',
        'spirit_shield',
        'pet_dark_core',
        'elysian_sigil',
        'spectral_sigil',
        'kill_count',
        'obtained',
        'arcane_sigil',
    ];

    protected $hidden = ['user_id'];
}
