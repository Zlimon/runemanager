<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Cerberus extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'hellpuppy',
        'eternal_crystal',
        'pegasian_crystal',
        'primordial_crystal',
        'jar_of_souls',
        'smouldering_stone',
        'key_master_teleport',
    ];

    protected $hidden = ['user_id'];
}
