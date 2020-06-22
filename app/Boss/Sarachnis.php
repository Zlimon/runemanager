<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Sarachnis extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'sarachnis_cudgel',
        'giant_egg_sac(full)',
        'kill_count',
        'obtained',
        'sraracha',
        'jar_of_eyes',
    ];

    protected $hidden = ['user_id'];
}
