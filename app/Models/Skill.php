<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
