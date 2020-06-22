<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Kreearra extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'armadyl_chestplate',
        'armadyl_chainskirt',
        'armadyl_helmet',
        'kill_count',
        'obtained',
        'godsword_shard_3',
        'godsword_shard_2',
        'pet_kreearra',
        'godsword_shard_1',
        'armadyl_hilt',
    ];

    protected $hidden = ['user_id'];
}
