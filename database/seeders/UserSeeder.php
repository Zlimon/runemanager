<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Zlimon',
            'email' => 'zlimon@runemanager.com',
            'email_verified_at' => now(),
            'password' => bcrypt('rune1234'),
            'remember_token' => Str::random(10),
            'icon_id' => Helper::randomItemId(true),
        ]);

        User::factory()
            ->count(10)
            ->create();
    }
}
