<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(UserAccountSeeder::class);
        $this->call(NewsPostSeeder::class);
        $this->call(CalendarSeeder::class);
        $this->call(ResourcePackSeeder::class);
    }
}
