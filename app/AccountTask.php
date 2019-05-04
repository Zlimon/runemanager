<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class AccountTask extends Model
{
    protected $fillable = [
        'account_id', 'task_id', 'status',
    ];

    public function member() {
        return $this->belongsTo(Account::class);
    }

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
