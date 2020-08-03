<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class CorporealBeast extends Model
{
    protected $table = 'corporeal_beast';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_dark_core',
        'elysian_sigil',
        'spectral_sigil',
        'arcane_sigil',
        'holy_elixir',
        'spirit_shield',
    ];

    protected $hidden = ['user_id'];
}
