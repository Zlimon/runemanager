<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GrotesqueGuardians extends Model
{
    protected $table = 'grotesque_guardians';

    protected $fillable = [
        'obtained',
        'kill_count',
        'noon',
        'black_tourmaline_core',
        'granite_gloves',
        'granite_ring',
        'granite_hammer',
        'jar_of_stone',
        'granite_dust',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
