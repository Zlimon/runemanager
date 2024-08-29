<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsPost>
 */
class NewsPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'news_category_id' => 1,
            'image_id' => 1,
            'title' => $this->faker->words(3, true),
            'shortstory' => $this->faker->sentence,
            'longstory' => $this->faker->text,
        ];
    }
}
