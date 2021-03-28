<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tempoross extends Model
{
    protected $table = 'tempoross';

    protected $fillable = [
        'obtained',
        'kill_count'
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
