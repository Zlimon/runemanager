<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StaticGameDataSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            RolesSeeder::class,
            DemoContentSeeder::class,
        ]);

        if (NewsPost::count() === 0) {
            NewsPost::factory(15)->create();
            $this->command->info('DatabaseSeeder: created 15 news posts.');
        }
    }
}
