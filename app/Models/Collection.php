<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $category_id
 * @property int $order
 * @property string $name
 * @property string $slug
 * @property string $model
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|Collection byCategorySlug($categorySlug)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereSlug($value)
 * @mixin \Eloquent
 */
class Collection extends Model
{
    protected $fillable = ['category_id', 'order', 'name', 'slug', 'model'];

    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeByCategorySlug($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

//    public function collection()
//    {
//        return $this->morphTo();
//    }
}
