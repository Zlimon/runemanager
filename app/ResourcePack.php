<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\ResourcePack
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereUrl($value)
 * @mixin \Eloquent
 * @property string $version
 * @property string $author
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereVersion($value)
 */
class ResourcePack extends Model
{
    use HasFactory;
}
