<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'mongodb';
    protected $primaryKey = 'account_id';
}
