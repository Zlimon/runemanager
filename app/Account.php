<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'username', 'rank', 'level', 'xp', 'private'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function accountTask() {
        return $this->hasMany(AccountTask::class);
    }
}
