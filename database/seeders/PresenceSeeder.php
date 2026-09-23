<?php

namespace Database\Seeders;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\UsernameHistory;
use App\Support\Instance;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Everything on the `accounts` table that only ever arrives from the RuneLite
 * plugin: who is online, where they are, what their orbs read, their clan rank,
 * and the names they used to go by. Without it the Live Map, the profile
 * minimap and the status orbs are empty on an otherwise full instance.
 *
 * It also settles the account set against the instance mode — Group Ironman
 * accounts in GROUP, unclaimed roster accounts in both roster modes — so it
 * runs after the content seeders have created everyone.
 *
 * Idempotent per account: a re-run fills the accounts a partial run missed and
 * leaves the ones it already touched alone.
 */
class PresenceSeeder extends Seeder
{
    /** Accounts whose plugin is pinging right now. */
    private const ONLINE_ACCOUNTS = 8;

    /** Of those, how many also share their position. The rest are online without the Live Map. */
    private const ON_MAP_ACCOUNTS = 5;

    /** Unclaimed roster accounts left for registration's claim flow. */
    private const ROSTER_PLACEHOLDERS = 4;

    /** Accounts carrying a recorded name change. */
    private const RENAMED_ACCOUNTS = 4;

    /**
     * Columns the plugin's presence pushes own. Listing them keeps the factory
     * states' output from overwriting an account's identity or stats.
     *
     * @var list<string>
     */
    private const PRESENCE_COLUMNS = [
        'last_seen_at',
        'activity', 'activity_icon', 'location', 'world_number',
        'world_x', 'world_y', 'world_plane', 'position_updated_at',
        'hitpoints', 'hitpoints_max', 'prayer', 'prayer_max',
        'run_energy', 'special_attack', 'vitals_updated_at',
    ];

    /**
     * An in-game clan ladder, highest first. Ranks are the smallint the plugin
     * pushes; titles are what the clan calls that rank.
     *
     * @var list<array{rank: int, title: string}>
     */
    private const CLAN_RANKS = [
        ['rank' => 126, 'title' => 'Owner'],
        ['rank' => 125, 'title' => 'Deputy Owner'],
        ['rank' => 100, 'title' => 'Administrator'],
        ['rank' => 80, 'title' => 'Organiser'],
        ['rank' => 60, 'title' => 'Coordinator'],
        ['rank' => 40, 'title' => 'General'],
        ['rank' => 25, 'title' => 'Corporal'],
        ['rank' => 10, 'title' => 'Recruit'],
    ];

    /**
     * Roster names the owner's plugin would have pushed ahead of anyone
     * registering. All are OSRS-legal (at most 12 characters, letters, digits
     * and single spaces) and none can collide with a factory-generated name,
     * which is always a stem followed by a number.
     *
     * @var list<string>
     */
    private const ROSTER_NAMES = ['Bramble Ox', 'Quiet Pete', 'Olm Enjoyer', 'Tindra', 'Salt Water'];

    /**
     * Names accounts used to go by, paired onto current accounts as a recorded
     * change. Same OSRS-legal shape as the roster names.
     *
     * @var list<string>
     */
    private const FORMER_NAMES = ['Zezimas Cat', 'Bronze Baron', 'Alt Number 2', 'Free Trade', 'Tutorial Isl'];

    public function run(): void
    {
        fake()->seed(DatabaseSeeder::FAKER_SEED);

        if (! Account::query()->exists()) {
            $this->command->warn('PresenceSeeder: no accounts found; run AccountSeeder and DemoContentSeeder first.');

            return;
        }

        $this->alignAccountTypes();
        $this->seedClanRanks();
        $this->seedRosterPlaceholders();
        $this->seedPresence();
        $this->seedUsernameHistory();
    }

    /**
     * A GROUP instance is a Group Ironman group, and `GroupIronmanValidator`
     * expects its accounts to be group ironmen. The generic factory spreads
     * account types across all five, so correct them to the one type the mode
     * allows.
     */
    private function alignAccountTypes(): void
    {
        if (! Instance::isGroup()) {
            return;
        }

        $corrected = 0;

        foreach (Account::query()->where('account_type', '!=', AccountTypesEnum::GROUP_IRONMAN->value)->get() as $account) {
            $account->forceFill(['account_type' => AccountTypesEnum::GROUP_IRONMAN])->save();
            $corrected++;
        }

        if ($corrected > 0) {
            $this->command->info(sprintf('PresenceSeeder: set %d accounts to group ironman.', $corrected));
        }
    }

    /**
     * Ranks and titles the way a clan holds them: one owner, one deputy, and
     * the rest spread down the ladder. Only claimed accounts get one — the
     * roster placeholders below carry their rank from the roster push instead.
     */
    private function seedClanRanks(): void
    {
        if (! Instance::isClan()) {
            return;
        }

        $accounts = $this->claimedAccounts()->whereNull('clan_rank')->values();

        if ($accounts->isEmpty()) {
            return;
        }

        foreach ($accounts as $index => $account) {
            // The top two ranks are held by exactly one account each; everyone
            // else lands somewhere in the ranks below them.
            $rank = $index < 2
                ? self::CLAN_RANKS[$index]
                : fake()->randomElement(array_slice(self::CLAN_RANKS, 2));

            $account->forceFill(['clan_rank' => $rank['rank'], 'clan_title' => $rank['title']])->save();
        }

        $this->command->info(sprintf('PresenceSeeder: ranked %d clan members.', $accounts->count()));
    }

    /**
     * Unclaimed accounts, as `SyncClanRoster` creates them from the owner's
     * roster push: a username and nothing else, waiting for the member to link
     * it on their first plugin login. Both roster modes gate registration on
     * claiming one, so both need some to claim.
     */
    private function seedRosterPlaceholders(): void
    {
        if (! Instance::requiresRosterClaim()) {
            return;
        }

        $existing = Account::query()->whereNull('user_id')->count();
        $type = Instance::isGroup() ? AccountTypesEnum::GROUP_IRONMAN : AccountTypesEnum::NORMAL;
        $created = 0;

        foreach (self::ROSTER_NAMES as $username) {
            if ($existing + $created >= self::ROSTER_PLACEHOLDERS) {
                break;
            }

            if (Account::query()->where('username', $username)->exists()) {
                continue;
            }

            $account = Account::rosterPlaceholder($username, $type);

            if (Instance::isClan()) {
                $rank = fake()->randomElement(array_slice(self::CLAN_RANKS, 2));
                $account->clan_rank = $rank['rank'];
                $account->clan_title = $rank['title'];
            }

            $account->save();
            $created++;
        }

        if ($created > 0) {
            $this->command->info(sprintf('PresenceSeeder: created %d unclaimed roster accounts.', $created));
        }
    }

    /**
     * Online presence, live position and status orbs, spread over a subset of
     * accounts so the instance reads as partly online rather than uniformly
     * logged in or uniformly asleep.
     */
    private function seedPresence(): void
    {
        // Against the targets, not against "has none yet": topping up from the
        // accounts still untouched would otherwise put another eight accounts
        // online on every run until the whole instance was logged in.
        $mapShortfall = max(0, self::ON_MAP_ACCOUNTS - Account::query()->whereNotNull('position_updated_at')->count());
        $busyShortfall = max(0, (self::ONLINE_ACCOUNTS - self::ON_MAP_ACCOUNTS)
            - Account::query()->whereNotNull('activity')->whereNull('position_updated_at')->count());

        $candidates = $this->claimedAccounts()->whereNull('position_updated_at')->whereNull('activity')->values();

        if ($candidates->isEmpty() || $mapShortfall + $busyShortfall === 0) {
            return;
        }

        $onMap = $candidates->take($mapShortfall);
        $online = $candidates->slice($mapShortfall, $busyShortfall);

        foreach ($onMap as $account) {
            $account->forceFill($this->presenceFor(
                $account,
                Account::factory()->online()->onMap()->withVitals(),
            ))->save();
        }

        foreach ($online as $account) {
            $account->forceFill($this->presenceFor(
                $account,
                Account::factory()->online()->busy(),
            ))->save();
        }

        $this->command->info(sprintf(
            'PresenceSeeder: %d accounts online, %d of them on the map.',
            $onMap->count() + $online->count(),
            $onMap->count(),
        ));
    }

    /**
     * The presence columns a factory state set, for an account that already
     * exists. Overriding `user_id` stops `raw()` expanding the definition's
     * nested `User::factory()` into a user nobody asked for.
     *
     * @return array<string, mixed>
     */
    private function presenceFor(Account $account, AccountFactory $factory): array
    {
        return Arr::only($factory->raw(['user_id' => $account->user_id]), self::PRESENCE_COLUMNS);
    }

    /**
     * Name changes the `plugin.account` middleware would have recorded: the
     * account's current username is the new one, so the profile's history reads
     * as "used to be X".
     */
    private function seedUsernameHistory(): void
    {
        $recorded = UsernameHistory::query()->distinct()->count('account_id');
        $shortfall = max(0, self::RENAMED_ACCOUNTS - $recorded);

        $accounts = $this->claimedAccounts()
            ->reject(fn (Account $account): bool => $account->usernameHistory()->exists())
            ->take($shortfall)
            ->values();

        foreach ($accounts as $index => $account) {
            UsernameHistory::factory()->create([
                'account_id' => $account->id,
                'old_username' => self::FORMER_NAMES[($recorded + $index) % count(self::FORMER_NAMES)],
                'new_username' => $account->username,
                'detected_at' => now()->subDays(fake()->numberBetween(7, 400)),
            ]);
        }

        if ($accounts->isNotEmpty()) {
            $this->command->info(sprintf('PresenceSeeder: recorded %d username changes.', $accounts->count()));
        }
    }

    /**
     * Accounts a member actually owns, oldest first. Roster placeholders are
     * left out: nobody is logged into an account nobody has claimed.
     *
     * @return Collection<int, Account>
     */
    private function claimedAccounts(): Collection
    {
        return Account::query()->whereNotNull('user_id')->orderBy('id')->get();
    }
}
