<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function collection()
    {
        return $this->hasMany(Collection::class);
    }

    public function broadcast()
    {
        return $this->hasMany(Broadcast::class);
    }

    public function log()
    {
        return $this->hasMany(Log::class);
    }
}
