<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DagannothPrime extends Model
{
    protected $table = 'dagannoth_prime';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
