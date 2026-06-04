<?php

namespace Database\Seeders;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\User;
use App\Services\Hiscores\HiscoresSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Throwable;

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
     * Demo OSRS usernames distributed across the demo users. Realistic-looking
     * names for browsing the UI; synced against the live hiscores API on a
     * best-effort basis (names that don't exist on hiscores keep their default zeroed stats).
     *
     * @var list<string>
     */
    private array $demoAccounts = [
        'Matilizo', 'wewantrings', 'Nex What', 'Far89', 'CinnaBawns',
        //        'Den falahid', 'Zuk Zillion', 'Dyla n fan', 'No Ni', 'Gronky444',
        //        'Q R U I Z', 'R O A D MAX6', 'a Gemidish55', 'Ms Effect', 'Max And Ruby',
        //        'l0ltit', 'l 54', 'WumbleTeed', 'A N U B I S', 'ZET0 KA1BA',
        //        'Jxclusiive22', 'Dynosauur', 'Location', 'Darkrune256', 'Dharoks ass',
        //        'Mrs Jesus', 'Spirit2k15', 'Kandarin Kin', 'Didg1t', 'Dessabrine',
        //        'spongejoe2', 'Skullzj102', 'ante xoxo', 'zorminis', 'PokketSand',
        //        'Fyressence', 'MezDoe', 'highlifeng9', 'Boonana95', 'Reshy HC',
        //        'Bigschmoo12', 'Jo Coolest', 'Aquat1cz', 'Varang', 'LOPrGds',
        //        'Wigwamjones', 'Unsponsored', 'Muswa', 'GoeieVanHaze', 'TobeTask',
    ];

    public function run(HiscoresSync $sync): void
    {
        $zlimon = User::where('email', 'zlimon@runemanager.com')->first();

        if (! $zlimon) {
            $this->command->error('AccountSeeder: Zlimon user not found. Run UserSeeder first.');

            return;
        }

        $existing = Account::count();
        if ($existing > 0) {
            $this->command->info(sprintf('AccountSeeder: %d accounts already present, skipping.', $existing));

            return;
        }

        $created = [];

        foreach ($this->zlimonAccounts as $spec) {
            $created[] = $this->createAccount($zlimon, $spec['name'], $spec['type']);
        }

        $demoUsers = User::where('id', '!=', $zlimon->id)->get();
        if ($demoUsers->isEmpty()) {
            $this->command->warn('AccountSeeder: no demo users found; only Zlimon accounts seeded.');
        } else {
            foreach ($this->demoAccounts as $name) {
                $owner = $demoUsers->random();
                $created[] = $this->createAccount($owner, $name, AccountTypesEnum::NORMAL);
            }
        }

        $this->command->info(sprintf('AccountSeeder: created %d accounts.', count($created)));
        $this->syncHiscores($sync, $created);
    }

    private function createAccount(User $user, string $username, AccountTypesEnum $type): Account
    {
        return Account::query()->create([
            'user_id' => $user->id,
            // RuneLite's Client.getAccountHash() returns a Java long; synthesise something
            // long-shaped for seed data. The plugin overwrites this with the real hash on first push.
            'account_hash' => 'seed-'.Str::lower(Str::random(20)),
            'account_type' => $type,
            'username' => $username,
            'rank' => 0,
            'level' => 0,
            'xp' => 0,
            'online' => (bool) random_int(0, 1),
            'last_seen_at' => random_int(0, 1) ? now()->subSeconds(random_int(0, 120)) : now()->subHours(random_int(1, 48)),
        ]);
    }

    /**
     * Best-effort fetch from the official OSRS hiscores API. Failures (404 for
     * names not on the hiscores, network blips) are reported but don't abort
     * the seed — the account stays around with zeroed stats.
     *
     * @param  list<Account>  $accounts
     */
    private function syncHiscores(HiscoresSync $sync, array $accounts): void
    {
        if ($accounts === []) {
            return;
        }

        $this->command->info(sprintf('AccountSeeder: fetching hiscores for %d accounts...', count($accounts)));

        $synced = 0;
        $missed = 0;

        foreach ($accounts as $account) {
            try {
                $sync->syncForAccount($account);
                $synced++;
            } catch (Throwable $e) {
                $missed++;
                $this->command->line(sprintf('  ✗ %s — %s', $account->username, $e->getMessage()));
            }
        }

        $this->command->info(sprintf('AccountSeeder: hiscores synced for %d, missed %d.', $synced, $missed));
    }
}
