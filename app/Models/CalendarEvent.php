<?php

namespace App\Models;

use App\Enums\CalendarEventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property CalendarEventType $event_type
 * @property string|null $color
 * @property int|null $item_id
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Collection<int, CalendarEventRsvp> $rsvps
 */
class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_type',
        'color',
        'item_id',
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

    /**
     * Attendance, one row per account that signed up.
     */
    public function rsvps(): HasMany
    {
        return $this->hasMany(CalendarEventRsvp::class);
    }

    /**
     * Whether the event is over. A multi-day event runs until its end, so it
     * only counts as past once `ends_at` has been and gone; a single-point
     * event is past the moment it starts.
     */
    public function hasPassed(): bool
    {
        return ($this->ends_at ?? $this->starts_at)?->isPast() ?? false;
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
