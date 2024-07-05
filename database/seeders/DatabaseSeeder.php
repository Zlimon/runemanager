<?php

namespace Database\Seeders;

use App\Helpers\ItemHelper;
use App\Models\Account;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(15)->withPersonalTeam()->has(Account::factory()->count(rand(1, 5)))->create();

        User::factory()->withPersonalTeam()->create([
            'name' => 'Zlimon',
            'email' => 'zlimon@runemanager.com',
            'email_verified_at' => now(),
            'password' => bcrypt('test1234'),
            'remember_token' => Str::random(10),
            'icon_id' => ItemHelper::randomItemId(false),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'current_team_id' => null,
        ]);

        // TODO refactor to use Storage facade instead
        if (!File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        Artisan::call(
            'resourcepack:fetch',
            [
                'name' => 'pack-osrs-dark',
                '--use' => 'yes',
            ]
        );

//        $this->call([
//            UserSeeder::class,
//        ]);
    }
}
