<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class TheInferno extends Model
{
    protected $table = 'the_inferno';

    protected $fillable = [
        'obtained',
        'kill_count',
        'jal-nib-rek',
        'infernal_cape',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
