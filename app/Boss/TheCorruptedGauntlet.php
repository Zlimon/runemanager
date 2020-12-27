<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheCorruptedGauntlet extends Model
{
    protected $table = 'the_corrupted_gauntlet';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
