<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\CalendarEventRsvp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEventRsvp>
 */
class CalendarEventRsvpFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calendar_event_id' => CalendarEvent::factory(),
            'account_id' => Account::factory(),
        ];
    }
}
