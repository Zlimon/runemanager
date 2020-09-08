<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DagannothSupreme extends Model
{
    protected $table = 'dagannoth_supreme';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
