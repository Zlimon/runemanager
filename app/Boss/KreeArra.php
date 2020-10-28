<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class Kreearra extends Model
{
    protected $table = 'kreearra';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kreearra',
        'armadyl_helmet',
        'armadyl_chestplate',
        'armadyl_chainskirt',
        'armadyl_hilt',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
