<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Mimic extends Model
{
    protected $table = 'mimic';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
