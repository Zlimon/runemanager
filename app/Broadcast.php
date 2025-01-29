<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'log_id',
        'type',
        'icon',
        'message',
    ];

    public function log()
    {
        return $this->belongsTo(Log::class);
    }
}
