<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function account()
    {
        return $this->belongsToMany(Account::class);
    }
}
