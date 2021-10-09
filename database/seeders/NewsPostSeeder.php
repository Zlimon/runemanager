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
            'shortstory' => 'RuneManager is a CMS developed for clans in Old School RuneScape',
            'longstory' => base64_encode('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
        ]);

        NewsPost::factory()
            ->count(10)
            ->create();
    }
}
