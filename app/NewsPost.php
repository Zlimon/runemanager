<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\NewsPost
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $image_id
 * @property string $title
 * @property string $shortstory
 * @property string $longstory
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Category $category
 * @property-read \App\Image $image
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereLongstory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereShortstory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereUserId($value)
 * @mixin \Eloquent
 */
class NewsPost extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'image_id', 'title', 'shortstory', 'longstory',
    ];

    public function user() {
        return $this->belongsToMany(User::class);
    }

    public function category() {
        return $this->belongsTo(NewsCategory::class);
    }

    public function image() {
        return $this->belongsTo(Image::class);
    }
}
