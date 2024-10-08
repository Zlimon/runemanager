<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Zlimon',
            'email' => 'zlimon@runemanager.com',
            'email_verified_at' => now(),
            'password' => bcrypt('test1234'),
            'remember_token' => Str::random(10),
            'icon_id' => Item::randomItemId(),
        ]);
    }
}
