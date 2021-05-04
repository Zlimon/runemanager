<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Collection
 *
 * @property string $name
 * @property string $alias
 * @property string $type
 * @property string $model
 * @property-read Model|\Eloquent $collection
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereType($value)
 * @mixin \Eloquent
 * @property int $id
 * @property int $category_id
 * @property int $order
 * @property string $slug
 * @property-read \App\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereSlug($value)
 */
class Collection extends Model
{
    protected $fillable = ['category_id', 'order', 'name', 'slug', 'model'];

    public $timestamps = false;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function findByNameAndCategory($name, $categoryId)
    {
        return self::where([['name', $name], ['category_id', $categoryId]])->first();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function collection()
    {
        return $this->morphTo();
    }
}
