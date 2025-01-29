<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Broadcast
 *
 * @property int $id
 * @property int $log_id
 * @property string $type
 * @property string $icon
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Log $log
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast query()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Broadcast extends Model
{
    protected $fillable = [
        'log_id',
        'type',
        'icon',
        'message',
    ];

    public function log()
    {
        return $this->belongsTo(Log::class);
    }
}
