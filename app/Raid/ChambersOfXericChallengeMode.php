<?php

namespace App\Raid;

use Illuminate\Database\Eloquent\Model;

class ChambersOfXericChallengeMode extends Model
{
    protected $table = 'chambers_of_xeric_challenge_mode';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
