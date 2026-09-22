<?php

namespace App\Models;

use App\Enums\CalendarEventType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'description',
    'event_type',
    'color',
    'item_id',
    'starts_at',
    'ends_at',
])]
class CalendarEvent extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    #[Scope]
    protected function past(Builder $query): void
    {
        $query->where('starts_at', '<', now())->orderByDesc('starts_at');
    }
}
