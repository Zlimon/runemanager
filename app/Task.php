<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function userTask() {
        return $this->hasMany(UserTask::class);
    }
}
