<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DerangedArchaeologist extends Model
{
    protected $table = 'deranged_archaeologist';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
