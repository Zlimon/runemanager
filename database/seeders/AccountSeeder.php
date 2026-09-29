<?php

namespace Database\Seeders;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the named dev/demo accounts. Offline by design — stats stay zeroed
 * here and are filled in by `DemoContentSeeder` (synthetic) or by
 * `LiveHiscoresSeeder` (real OSRS hiscores, opt-in).
 */
class AccountSeeder extends Seeder
{
    /**
     * The dev user's three known OSRS accounts. Used by the plugin testing flow.
     *
     * @var array<int, array{name: string, type: AccountTypesEnum}>
     */
    private array $zlimonAccounts = [
        ['name' => 'Habski', 'type' => AccountTypesEnum::IRONMAN],
        ['name' => 'Marni', 'type' => AccountTypesEnum::NORMAL],
        ['name' => 'Hey Jase', 'type' => AccountTypesEnum::NORMAL],
    ];

    /**
     * Real, active OSRS accounts tracked on Wise Old Man, distributed across
     * the demo users so a live WOM sync has genuine history to pull. Nine of
     * them belong to WOM group 145 ("Iron Clan"), which makes group-level data
     * demoable too. Verified active on 2026-09-26; their stats stay synthetic
     * unless `LiveHiscoresSeeder` is run explicitly.
     *
     * @var array<int, array{name: string, type: AccountTypesEnum}>
     */
    private array $demoAccounts = [
        ['name' => 'Bartheez', 'type' => AccountTypesEnum::IRONMAN],
        ['name' => 'kill the ge', 'type' => AccountTypesEnum::IRONMAN],
        ['name' => '768lb man', 'type' => AccountTypesEnum::IRONMAN],
        ['name' => 'viking piggi', 'type' => AccountTypesEnum::IRONMAN],
        ['name' => 'Musandam', 'type' => AccountTypesEnum::ULTIMATE_IRONMAN],
        ['name' => 'L apras', 'type' => AccountTypesEnum::ULTIMATE_IRONMAN],
        ['name' => 'Ju1es', 'type' => AccountTypesEnum::HARDCORE_IRONMAN],
        ['name' => 'Pewws Peon', 'type' => AccountTypesEnum::NORMAL],
        ['name' => 'Huisbaas Ali', 'type' => AccountTypesEnum::NORMAL],
        ['name' => 'Meekss', 'type' => AccountTypesEnum::NORMAL],
        ['name' => 'santi osrs', 'type' => AccountTypesEnum::NORMAL],
    ];

    public function run(): void
    {
        $zlimon = User::where('email', 'zlimon@runemanager.com')->first();

        if (! $zlimon) {
            $this->command->error('AccountSeeder: Zlimon user not found. Run UserSeeder first.');

            return;
        }

        $created = 0;

        foreach ($this->zlimonAccounts as $spec) {
            $created += (int) $this->ensureAccount($zlimon, $spec['name'], $spec['type']);
        }

        $demoUsers = User::where('id', '!=', $zlimon->id)->get();
        if ($demoUsers->isEmpty()) {
            $this->command->warn('AccountSeeder: no demo users found; only Zlimon accounts seeded.');
        } else {
            foreach ($this->demoAccounts as $spec) {
                $created += (int) $this->ensureAccount($demoUsers->random(), $spec['name'], $spec['type']);
            }
        }

        $this->command->info(sprintf(
            'AccountSeeder: created %d accounts (%d total).',
            $created,
            Account::count(),
        ));
    }

    /**
     * Create the account unless that username is already taken, so a re-run
     * fills gaps instead of skipping the whole seeder.
     *
     * @return bool whether a row was written
     */
    private function ensureAccount(User $user, string $username, AccountTypesEnum $type): bool
    {
        if (Account::query()->where('username', $username)->exists()) {
            return false;
        }

        // Account carries no #[Fillable] and is therefore totally guarded. Every
        // writer in the app (HiscoresSync, the plugin controllers, StoreAvatar)
        // goes through forceFill; seed data is no exception.
        (new Account)->forceFill([
            'user_id' => $user->id,
            // RuneLite's Client.getAccountHash() returns a Java long; synthesise something
            // long-shaped for seed data. The plugin overwrites this with the real hash on first push.
            'account_hash' => 'seed-'.Str::lower(Str::random(20)),
            'account_type' => $type,
            'username' => $username,
            'rank' => 0,
            'level' => 0,
            'xp' => 0,
            'last_seen_at' => fake()->boolean()
                ? now()->subSeconds(fake()->numberBetween(0, 120))
                : now()->subHours(fake()->numberBetween(1, 48)),
        ])->save();

        return true;
    }
}
