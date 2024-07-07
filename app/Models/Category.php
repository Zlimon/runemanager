<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Category
 *
 * @property int $id
 * @property string $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Broadcast[] $broadcast
 * @property-read int|null $broadcast_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Collection[] $collection
 * @property-read int|null $collection_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $log
 * @property-read int|null $log_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Collection> $collections
 * @property-read int|null $collections_count
 * @mixin \Eloquent
 */
class Category extends Model
{
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class, 'category_id');
    }

//    public function broadcast()
//    {
//        return $this->hasMany(Broadcast::class);
//    }
//
//    public function log()
//    {
//        return $this->hasMany(Log::class);
//    }
}
