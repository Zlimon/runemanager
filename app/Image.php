<?php

namespace RuneManager;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'image_file_name', 'image_file_extension', 'image_file_type', 'image_file_size'
    ];

    public function image() {
        return $this->hasMany(NewsPost::class);
    }
}
