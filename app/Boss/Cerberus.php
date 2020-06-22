<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Cerberus extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'pegasian_crystal',
        'primordial_crystal',
        'eternal_crystal',
        'smouldering_stone',
        'hellpuppy',
        'kill_count',
        'key_master_teleport',
        'obtained',
        'jar_of_souls',
    ];

    protected $hidden = ['user_id'];
}
