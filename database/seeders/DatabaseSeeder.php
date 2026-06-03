<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OsrsboxStaticSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
        ]);

        if (NewsPost::count() === 0) {
            NewsPost::factory(15)->create();
            $this->command->info('DatabaseSeeder: created 15 news posts.');
        }

        //        $this->tryFetchDefaultResourcePack();
    }

    /**
     * Best-effort: install the sample resource pack and set it as the instance
     * default so unauthenticated visitors and users with no override see it.
     * Wrapped in try/catch so a network blip doesn't kill the whole seed.
     */
    private function tryFetchDefaultResourcePack(): void
    {
        try {
            Artisan::call('resourcepack:fetch', ['name' => 'sample-vanilla']);
            Artisan::call('resourcepack:switch', ['name' => 'sample-vanilla']);
            $this->command->info('DatabaseSeeder: installed sample-vanilla as the instance default.');
        } catch (\Throwable $e) {
            $this->command->warn(sprintf('DatabaseSeeder: resource pack install failed (continuing): %s', $e->getMessage()));
        }
    }
}
