<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @mixin \Eloquent
 */
class Category extends Model
{
    public function collection()
    {
        return $this->hasMany(Collection::class);
    }

    public function broadcast()
    {
        return $this->hasMany(Broadcast::class);
    }

    public function log()
    {
        return $this->hasMany(Log::class);
    }
}
