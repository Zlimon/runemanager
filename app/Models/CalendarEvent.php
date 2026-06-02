<?php

namespace App\Models;

use App\Enums\CalendarEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property CalendarEventType $event_type
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_type',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => CalendarEventType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('starts_at', '<', now())->orderByDesc('starts_at');
    }
}
