<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        User::factory(15)->withPersonalTeam()->has(Account::factory()->count(rand(1, 5)))->create();
        User::factory(15)->withPersonalTeam()->create();

        User::factory()->withPersonalTeam()->create([
            'name' => 'Zlimon',
            'email' => 'zlimon@runemanager.com',
            'email_verified_at' => now(),
            'password' => bcrypt('test1234'),
            'remember_token' => Str::random(10),
            'icon_id' => Item::randomItemId(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'current_team_id' => null,
        ]);

        $this->call([
            CollectionSeeder::class,
        ]);

        Artisan::call('migrate');

        $this->call([
            AccountSeeder::class,
        ]);

        Artisan::call(
            'resourcepack:fetch',
            [
                'name' => 'sample-vanilla',
                '--use' => 'yes',
            ]
        );

        NewsPost::factory(15)->create();
    }
}
