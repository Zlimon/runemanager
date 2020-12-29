<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Category
 *
 * @property int $id
 * @property string $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\NewsPost[] $newsPost
 * @property-read int|null $news_post_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    public function newsPost() {
        return $this->hasMany(NewsPost::class);
    }
}
