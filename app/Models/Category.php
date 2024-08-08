<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::slug($value),
            set: fn (string $value) => Str::slug($value),
        );
    }

    public function collections(): HasMany
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
