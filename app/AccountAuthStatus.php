<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountAuthStatus extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }
}
