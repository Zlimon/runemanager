<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class DagannothRex extends Model
{
    protected $table = 'dagannoth_rex';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account() {
        return $this->belongsTo(\App\Account::class);
    }
}
