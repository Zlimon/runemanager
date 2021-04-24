<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['category_id', 'name', 'alias', 'model'];

    public $timestamps = false;

    public static function findByNameAndCategory($name, $category_id)
    {
        return self::where([['name', $name], ['category_id', $category_id]])->first();
    }

    public function category()
    {
        return $this->hasOne(Category::class);
    }

    public function collection()
    {
        return $this->morphTo();
    }
}
