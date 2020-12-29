<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Image
 *
 * @property int $id
 * @property string $image_file_name
 * @property string $image_file_extension
 * @property string $image_file_type
 * @property int $image_file_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\NewsPost[] $image
 * @property-read int|null $image_count
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageFileExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Image extends Model
{
    protected $fillable = [
        'image_file_name',
        'image_file_extension',
        'image_file_type',
        'image_file_size'
    ];

    public function image()
    {
        return $this->hasMany(NewsPost::class);
    }
}
