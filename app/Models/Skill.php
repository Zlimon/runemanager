<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill
 *
 * @property int $id
 * @property int $category_id
 * @property int $order
 * @property string $name
 * @property string $slug
 * @property string $model
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skill whereSlug($value)
 * @mixin \Eloquent
 */
class Skill extends Model
{
    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
