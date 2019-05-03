<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    protected $fillable = [
        'user_id', 'task_id', 'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
