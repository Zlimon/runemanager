<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function newsPost() {
        return $this->hasMany(NewsPost::class);
    }
}
