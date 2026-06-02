<?php

namespace Database\Factories;

use App\Enums\CalendarEventType;
use App\Models\CalendarEvent;
use App\Models\User;
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
}
