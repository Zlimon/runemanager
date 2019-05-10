<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'image_id', 'title', 'shortstory', 'longstory',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function image() {
        return $this->belongsTo(Image::class);
    }
}
