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
 */
class Collection extends Model
{
    protected $fillable = ['category_id', 'name', 'alias', 'model'];

    public $timestamps = false;

    public static function findByNameAndCategory($name, $category_id)
    {
        return self::where([['name', $name], ['category_id', $category_id]])->firstOrFail();
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
