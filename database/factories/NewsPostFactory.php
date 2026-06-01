<?php

namespace Database\Factories;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'news_category_id' => DB::table('news_categories')->inRandomOrder()->value('id') ?? 1,
            'image_id' => 1,
            'title' => $this->faker->words(3, true),
            'shortstory' => $this->faker->sentence,
            'longstory' => $this->faker->text,
        ];
    }
}
