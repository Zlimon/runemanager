<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Obor extends Model
{
    protected $table = 'obor';

    protected $fillable = [
        'obtained',
        'kill_count',
        'hill_giant_club',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
