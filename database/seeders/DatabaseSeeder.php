<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\NewsPost;
use App\Models\Quest;
use App\Models\ResourcePack;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Throwable;

class DatabaseSeeder extends Seeder
{
    /**
     * Fixed faker seed so a demo instance is reproducible from one run to the
     * next. Bump it only when the demo data is meant to change.
     */
    public const FAKER_SEED = 20260923;

    public function run(): void
    {
        fake()->seed(self::FAKER_SEED);

        // `migrate:fresh --seed` is one process: anything that read a setting
        // before the tables were dropped left the memo holding rows that no
        // longer exist, and SettingsSeeder would then skip every key it thinks
        // is already set. Drop it before the first read.
        Setting::flushMemo();

        $this->clearStaleSnapshots();

        $this->call([
            StaticGameDataSeeder::class,
            // Writes instance_configured / instance_mode first: without it every
            // page renders SetupInProgress no matter how much data sits behind it.
            SettingsSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            RolesSeeder::class,
            DemoContentSeeder::class,
            DemoSnapshotsSeeder::class,
        ]);

        if (NewsPost::count() === 0) {
            NewsPost::factory(15)->create();
            $this->command->info('DatabaseSeeder: created 15 news posts.');
        }

        $this->reportResourcePacks();
    }

    /**
     * `migrate:fresh` drops the SQL tables but cannot reach MongoDB, so an
     * earlier demo's snapshots outlive it — and because account ids start over
     * at 1, those documents silently re-attach to whichever new account lands
     * on the same id. Clearing them is only safe (and only necessary) when the
     * relational side is genuinely empty, which is exactly the fresh-seed case.
     *
     * Never runs in production, where an empty accounts table means a mode
     * switch wiped the roster and the snapshots are ResetInstanceData's to
     * remove, not a seeder's.
     */
    private function clearStaleSnapshots(): void
    {
        if (app()->environment('production') || Account::query()->exists()) {
            return;
        }

        try {
            foreach ([Bank::class, CollectionLog::class, GroupBank::class, Inventory::class, Loot::class, LootingBag::class, Quest::class] as $model) {
                $model::query()->delete();
            }
        } catch (Throwable $e) {
            $this->command->warn('DatabaseSeeder: could not clear old MongoDB snapshots — '.$e->getMessage());

            return;
        }

        $this->command->info('DatabaseSeeder: cleared MongoDB snapshots left by an earlier demo.');
    }

    /**
     * Resource-pack sprites are gitignored and downloaded at runtime, so the
     * seed deliberately installs none. Say so, rather than leaving the themes
     * and packs pages mysteriously empty.
     */
    private function reportResourcePacks(): void
    {
        if (ResourcePack::count() > 0) {
            return;
        }

        $this->command->warn('DatabaseSeeder: no resource packs installed — Themes and Admin → Packs will be empty.');
        $this->command->line('  Install the bundled Vanilla baseline (needs network) with:');
        $this->command->line('    ./vendor/bin/sail artisan resourcepack:ensure-vanilla');
    }
}
