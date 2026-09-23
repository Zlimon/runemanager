<?php

namespace Database\Factories;

use App\Enums\CalendarEventType;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Support\Instance;
use Database\Seeders\Support\ItemPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('+1 day', '+1 month');

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'event_type' => $this->faker->randomElement(CalendarEventType::cases()),
            // Both are optional in the form, so leave some events without them.
            'color' => $this->faker->boolean(75)
                ? $this->faker->randomElement(StoreCalendarEventRequest::COLORS)
                : null,
            // The item the event is about (a boss drop, a skilling goal), shown
            // as its sprite beside the entry.
            'item_id' => $this->faker->boolean(50) ? (ItemPool::id($this->faker) ?: null) : null,
            'starts_at' => $startsAt,
            'ends_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween($startsAt, '+2 months') : null,
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'starts_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'ends_at' => null,
        ]);
    }

    /**
     * Painted in a colour the instance treats as important, so the calendar's
     * highlight actually highlights something. Follows the configured list
     * rather than hard-coding red, which is only the default.
     */
    public function highlighted(): static
    {
        $colors = Instance::calendarHighlightColors() ?: Instance::DEFAULT_CALENDAR_HIGHLIGHT_COLORS;

        return $this->state(fn () => ['color' => $this->faker->randomElement($colors)]);
    }
}
