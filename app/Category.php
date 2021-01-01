<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function collection()
    {
        return $this->hasMany(Collection::class);
    }

    public function notification()
    {
        return $this->hasMany(Notification::class);
    }
}
