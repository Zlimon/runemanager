<?php

namespace Database\Seeders;

use App\NewsPost;
use Illuminate\Database\Seeder;

class NewsPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NewsPost::create([
            'user_id' => 1,
            'news_category_id' => 1,
            'image_id' => 1,
            'title' => 'Welcome to RuneManager!',
            'shortstory' => 'Welcome to RuneManager!',
            'longstory' => 'Welcome to RuneManager!',
        ]);
    }
}
