<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class GiantMole extends Model
{
    protected $table = 'giant_mole';

    protected $fillable = [
        'obtained',
        'kill_count',
        'baby_mole',
        'mole_skin',
        'mole_claw',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
