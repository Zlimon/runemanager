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

        $this->tryFetchDefaultResourcePack();
    }

    /**
     * Best-effort: pull the sample resource pack so the UI has something to render
     * with. Wrapped in try/catch so a network blip doesn't kill the whole seed.
     */
    private function tryFetchDefaultResourcePack(): void
    {
        try {
            Artisan::call('resourcepack:fetch', [
                'name' => 'sample-vanilla',
                '--use' => 'yes',
            ]);
            $this->command->info('DatabaseSeeder: fetched sample-vanilla resource pack.');
        } catch (\Throwable $e) {
            $this->command->warn(sprintf('DatabaseSeeder: resourcepack:fetch failed (continuing): %s', $e->getMessage()));
        }
    }
}
