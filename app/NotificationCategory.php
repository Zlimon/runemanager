<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\NotificationCategory
 *
 * @property int $id
 * @property string $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Notification[] $notification
 * @property-read int|null $notification_count
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationCategory whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationCategory whereId($value)
 * @mixin \Eloquent
 */
class NotificationCategory extends Model
{
    public function notification() {
        return $this->hasMany(Notification::class);
    }
}
