<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\NewsPost
 *
 * @property int $id
 * @property int $user_id
 * @property int $news_category_id
 * @property int $image_id
 * @property string $title
 * @property string $shortstory
 * @property string $longstory
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Image $image
 * @property-read \App\NewsCategory $newsCategory
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereLongstory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereNewsCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereShortstory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsPost whereUserId($value)
 * @mixin \Eloquent
 */
class NewsPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'news_category_id', 'image_id', 'title', 'shortstory', 'longstory',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function newsCategory() {
        return $this->belongsTo(NewsCategory::class);
    }

    public function image() {
        return $this->belongsTo(Image::class);
    }
}
