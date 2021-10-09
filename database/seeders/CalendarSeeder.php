<?php

namespace Database\Seeders;

use App\Calendar;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Calendar::factory()
            ->count(10)
            ->create();
    }
}
