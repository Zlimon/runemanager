<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    public function newsPost()
    {
        return $this->hasMany(NewsPost::class);
    }
}
