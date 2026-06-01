<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'zlimon@runemanager.com')->exists()) {
            $this->command->info('UserSeeder: Zlimon already present, skipping.');

            return;
        }

        $iconId = Item::randomItemId();

        // The dev user.
        User::query()->forceCreate([
            'name' => 'Zlimon',
            'email' => 'zlimon@runemanager.com',
            'email_verified_at' => now(),
            'password' => Hash::make('test1234'),
            'remember_token' => Str::random(10),
            'icon_id' => $iconId,
        ]);

        // 15 demo users for populating leaderboards / browsing UI.
        User::factory(15)->withPersonalTeam()->create();

        $this->command->info(sprintf(
            'UserSeeder: created Zlimon + 15 demo users (%d users total).',
            User::count(),
        ));
    }
}
