<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SPEC §10 — an account's attendance on a calendar event. The row's existence
 * *is* the RSVP; un-RSVPing deletes it.
 *
 * @property int $id
 * @property int $calendar_event_id
 * @property int $account_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read CalendarEvent $calendarEvent
 * @property-read Account $account
 */
class CalendarEventRsvp extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_event_id',
        'account_id',
    ];

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
