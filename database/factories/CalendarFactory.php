<?php

namespace Database\Factories;

use App\Calendar;
use App\Helpers\Helper;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Calendar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->pluck('id')->first(),
            'title' => $this->faker->text(50),
            'description' => $this->faker->text(100),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 months'),
            'end_date' => $this->faker->dateTimeBetween('+4 hours', '+7 days'),
            'icon_id' => Helper::randomItemId(true),
            'event_color' => $this->faker->hexColor,
        ];
    }
}
