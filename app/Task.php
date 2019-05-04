<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function accountTask() {
        return $this->hasMany(AccountTask::class);
    }
}
