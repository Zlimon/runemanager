<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    protected $fillable = ['category_id', 'order', 'name', 'slug', 'model'];

    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function findByNameAndCategory(string $name, int $categoryId)
    {
        return self::where([['name', $name], ['category_id', $categoryId]])->first();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

//    public function collection()
//    {
//        return $this->morphTo();
//    }
}
