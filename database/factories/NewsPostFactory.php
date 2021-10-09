<?php

namespace Database\Factories;

use App\Image;
use App\NewsCategory;
use App\NewsPost;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NewsPost::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->pluck('id')->first(),
            'news_category_id' => NewsCategory::inRandomOrder()->pluck('id')->first(),
            'image_id' => Image::inRandomOrder()->pluck('id')->first(),
            'title' => $this->faker->text(50),
            'shortstory' => $this->faker->text(100),
            'longstory' => base64_encode($this->faker->realText),
        ];
    }
}
