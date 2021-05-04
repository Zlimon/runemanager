<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\NewsCategory
 *
 * @property int $id
 * @property string $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\NewsPost[] $newsPost
 * @property-read int|null $news_post_count
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory whereId($value)
 * @mixin \Eloquent
 */
class NewsCategory extends Model
{
    public function newsPost()
    {
        return $this->hasMany(NewsPost::class);
    }
}
