<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CollectionLog;
use App\Models\GroupBank;
use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Instance;
use Database\Factories\CollectionLogFactory;
use Database\Factories\GroupBankFactory;
use Database\Factories\ResourcePackFactory;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * The demo surfaces that hang off documents and rows nothing else writes:
 * collection logs, the Group Ironman group bank, and the installed resource
 * packs. Runs after `DemoContentSeeder`, because every one of them attaches to
 * accounts or users that seeder has finished creating.
 *
 * Kept apart from `DemoContentSeeder` rather than folded into it because these
 * three are the parts that can fail on their own: the first two write to the
 * `mongodb` connection, which a degraded environment may not have at all, and
 * the third creates rows whose files are downloaded at runtime and therefore
 * never present in a fresh checkout. Each is skipped with one warning instead
 * of aborting a seed that is otherwise complete.
 *
 * Idempotent per account and per row, so a re-run fills gaps.
 */
class DemoSnapshotsSeeder extends Seeder
{
    /** Accounts given a collection log, out of the 25 the demo seeds. */
    private const COLLECTION_LOG_ACCOUNTS = 15;

    /** Demo packs installed alongside the Vanilla baseline. */
    private const DEMO_PACKS = 2;

    /** Users given a personal pack override, to demo the override hierarchy. */
    private const USERS_WITH_PACK = 3;

    public function run(): void
    {
        // Same fixed seed as the rest of the demo: this seeder is reproducible
        // whether it runs inside DatabaseSeeder or on its own.
        fake()->seed(DatabaseSeeder::FAKER_SEED);

        $this->seedCollectionLogs();
        $this->seedGroupBank();
        $this->seedResourcePacks();
    }

    /**
     * Collection logs for a majority of accounts, so the profile panel and the
     * collection-log leaderboard both have a real spread rather than one entry.
     *
     * The accounts without one are as deliberate as the accounts with one: a
     * player who has never synced TempleOSRS is the common case, and the panel
     * has to render for them too.
     */
    private function seedCollectionLogs(): void
    {
        $accounts = Account::query()->orderBy('id')->pluck('id');

        if ($accounts->isEmpty()) {
            $this->command->warn('DemoSnapshotsSeeder: no accounts found; run DemoContentSeeder first.');

            return;
        }

        $chosen = fake()->randomElements(
            $accounts->all(),
            min(self::COLLECTION_LOG_ACCOUNTS, $accounts->count()),
        );

        $created = 0;

        try {
            // `collection_log` is unique on account_id, so an account that
            // already has one is skipped rather than re-created.
            $existing = CollectionLog::query()
                ->whereIn('account_id', $chosen)
                ->pluck('account_id')
                ->all();

            foreach ($chosen as $index => $accountId) {
                if (in_array($accountId, $existing, true)) {
                    continue;
                }

                // One account near-completes the log, so the leaderboard has a
                // clear top rather than fifteen indistinguishable partials.
                $factory = $index === 0
                    ? CollectionLogFactory::new()->completionist()
                    : CollectionLogFactory::new();

                $factory->create(['account_id' => $accountId]);
                $created++;
            }
        } catch (Throwable $e) {
            $this->warnMongoUnavailable('collection logs', $e);

            return;
        }

        $this->command->info(sprintf('DemoSnapshotsSeeder: created %d collection logs.', $created));
    }

    /**
     * The group bank is a singleton document and only GROUP instances have a
     * page for it, so in the other two modes there is nothing to seed.
     */
    private function seedGroupBank(): void
    {
        if (! Instance::isGroup()) {
            return;
        }

        try {
            if (GroupBank::query()->exists()) {
                return;
            }

            GroupBankFactory::new()->create();
        } catch (Throwable $e) {
            $this->warnMongoUnavailable('the group bank', $e);

            return;
        }

        $this->command->info('DemoSnapshotsSeeder: created the group bank.');
    }

    /**
     * Pack rows for the theme picker and Admin → Packs, plus the per-user
     * overrides that make the pack hierarchy (personal pick → instance default
     * → Vanilla → no pack) visible without installing anything.
     */
    private function seedResourcePacks(): void
    {
        if (ResourcePack::query()->exists()) {
            SettingsSeeder::linkDefaultResourcePack();

            return;
        }

        ResourcePackFactory::new()->vanilla()->create();
        $demoPacks = ResourcePackFactory::new()->count(self::DEMO_PACKS)->create();

        $this->seedUserPackOverrides($demoPacks->pluck('id')->all());

        $default = SettingsSeeder::linkDefaultResourcePack();

        $this->command->info(sprintf(
            'DemoSnapshotsSeeder: installed %d resource packs (default: %s).',
            self::DEMO_PACKS + 1,
            $default?->alias ?: 'none',
        ));

        // Rows alone give the picker content but no textures: the sprites are
        // gitignored and fetched at runtime.
        $this->command->line('  Pack sprites are downloaded at runtime; fetch the Vanilla baseline with:');
        $this->command->line('    ./vendor/bin/sail artisan resourcepack:ensure-vanilla');
    }

    /**
     * Give a few members a personal pack and one member none at all. The owner
     * (the oldest user) is left on the instance default, which is the state an
     * admin browsing Settings → General expects to see.
     *
     * @param  list<int>  $packIds
     */
    private function seedUserPackOverrides(array $packIds): void
    {
        if ($packIds === []) {
            return;
        }

        $users = User::query()
            ->orderBy('id')
            ->skip(1)
            ->take(self::USERS_WITH_PACK + 1)
            ->get();

        foreach ($users as $index => $user) {
            if ($index < self::USERS_WITH_PACK) {
                $user->resource_pack_id = $packIds[$index % count($packIds)];
            } else {
                // `disable_resource_pack` is not fillable; it is toggled from
                // the profile, and the seeder writes it the same way the rest
                // of the app writes guarded columns.
                $user->disable_resource_pack = true;
            }

            $user->save();
        }
    }

    private function warnMongoUnavailable(string $what, Throwable $e): void
    {
        $this->command->warn(sprintf('DemoSnapshotsSeeder: could not seed %s — %s', $what, $e->getMessage()));
        $this->command->line('  Seeding continues; start the mongodb service and re-run db:seed to fill them in.');
    }
}
