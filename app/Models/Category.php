<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public function collection(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

//    public function broadcast()
//    {
//        return $this->hasMany(Broadcast::class);
//    }
//
//    public function log()
//    {
//        return $this->hasMany(Log::class);
//    }
}
