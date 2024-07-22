<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Npc extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'monsters';
    protected $primaryKey = 'id';
}
