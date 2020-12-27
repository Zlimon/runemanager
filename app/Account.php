<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'account_type', 'username', 'rank', 'level', 'xp'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function notification() {
        return $this->hasMany(Notification::class);
    }
}
