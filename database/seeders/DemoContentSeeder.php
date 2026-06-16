<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\AccountDiary;
use App\Models\AccountHiscore;
use App\Models\Announcement;
use App\Models\Bank;
use App\Models\CalendarEvent;
use App\Models\Equipment;
use App\Models\FeedEvent;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Support\CombatAchievements;
use Illuminate\Database\Seeder;

/**
 * Fills a dev instance with realistic, offline (no live API) data across the
 * whole app: per-account stats/diaries/combat achievements/equipment/quests/
 * inventory/bank/loot, plus instance-wide announcements, calendar events and a
 * live feed. Idempotent — each piece is skipped if it already exists, so it's
 * safe to re-run and only fills gaps left by the live-sync AccountSeeder.
 */
class DemoContentSeeder extends Seeder
{
    /** Extra synthetic accounts to flesh out the leaderboards. */
    private const TARGET_ACCOUNTS = 25;

    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('DemoContentSeeder: no users found; run UserSeeder first.');

            return;
        }

        $this->topUpAccounts($users);

        $accounts = Account::all();
        foreach ($accounts as $account) {
            $this->seedAccount($account);
        }
        $this->command->info(sprintf('DemoContentSeeder: populated %d accounts.', $accounts->count()));

        $this->seedAnnouncements($users);
        $this->seedCalendar($users);
        $this->seedFeed($accounts);
    }

    /** Create extra factory accounts so the leaderboards aren't sparse. */
    private function topUpAccounts($users): void
    {
        $missing = self::TARGET_ACCOUNTS - Account::count();
        if ($missing <= 0) {
            return;
        }

        Account::factory()->count($missing)->make()->each(function (Account $account) use ($users): void {
            $account->user_id = $users->random()->id;
            $account->save();
        });
        $this->command->info(sprintf('DemoContentSeeder: added %d synthetic accounts.', $missing));
    }

    private function seedAccount(Account $account): void
    {
        if (! $account->hiscore) {
            $hiscore = AccountHiscore::factory()->create(['account_id' => $account->id]);
            $overall = $hiscore->entries['skills']['overall'] ?? ['rank' => 0, 'level' => 0, 'xp' => 0];
            // Denormalise overall onto the account, like HiscoresSync does, so
            // account cards / top-accounts / overall board have data.
            $account->update([
                'rank' => $overall['rank'],
                'level' => $overall['level'],
                'xp' => $overall['xp'],
            ]);
        }

        if (! $account->diary) {
            AccountDiary::factory()->create(['account_id' => $account->id]);
        }

        if (! $account->combatAchievement) {
            AccountCombatAchievement::factory()->create([
                'account_id' => $account->id,
                ...$this->combatAchievementAttributes(),
            ]);
        }

        if (! $account->equipment) {
            Equipment::factory()->create(['account_id' => $account->id]);
        }

        if (! Quest::where('account_id', $account->id)->exists()) {
            Quest::factory()->create(['account_id' => $account->id]);
        }

        if (! Inventory::where('account_id', $account->id)->exists()) {
            Inventory::factory()->create(['account_id' => $account->id]);
        }

        if (! Bank::where('account_id', $account->id)->exists()) {
            Bank::factory()->create(['account_id' => $account->id]);
        }

        // Looting bag only on some accounts (PvP/wildy gear).
        if (random_int(0, 2) === 0 && ! LootingBag::where('account_id', $account->id)->exists()) {
            LootingBag::factory()->create(['account_id' => $account->id]);
        }

        if (! Loot::where('account_id', $account->id)->exists()) {
            Loot::factory()->count(random_int(4, 14))->create(['account_id' => $account->id]);
        }
    }

    /** Realistic, difficulty-decreasing per-tier counts + matching points. */
    private function combatAchievementAttributes(): array
    {
        $tiers = [];
        $points = 0;
        $pointValue = ['easy' => 1, 'medium' => 2, 'hard' => 3, 'elite' => 4, 'master' => 5, 'grandmaster' => 6];

        foreach (CombatAchievements::TIER_TOTALS as $tier => $total) {
            $done = random_int(0, $total);
            $tiers[$tier] = $done;
            $points += $done * $pointValue[$tier];
        }

        return ['points' => $points, 'tiers' => $tiers];
    }

    private function seedAnnouncements($users): void
    {
        if (Announcement::count() > 0) {
            return;
        }

        Announcement::factory()->count(5)->create(['user_id' => $users->random()->id]);
        Announcement::factory()->expired()->count(2)->create(['user_id' => $users->random()->id]);
        $this->command->info('DemoContentSeeder: created 7 announcements.');
    }

    private function seedCalendar($users): void
    {
        if (CalendarEvent::count() > 0) {
            return;
        }

        CalendarEvent::factory()->count(8)->create(['user_id' => $users->random()->id]);
        CalendarEvent::factory()->past()->count(4)->create(['user_id' => $users->random()->id]);
        $this->command->info('DemoContentSeeder: created 12 calendar events.');
    }

    private function seedFeed($accounts): void
    {
        if (FeedEvent::count() > 0) {
            return;
        }

        $lootIds = Item::randomItemIds(20) ?: [11286];

        foreach ($accounts as $account) {
            FeedEvent::factory()->count(random_int(1, 3))->create(['account_id' => $account->id]);
            FeedEvent::factory()->questComplete()->create(['account_id' => $account->id]);
            FeedEvent::factory()->combatAchievement()->create(['account_id' => $account->id]);

            // Loot drops with real item ids so they hydrate on the feed.
            FeedEvent::factory()->lootDrop()->create([
                'account_id' => $account->id,
                'payload' => [
                    'source' => collect(['Zulrah', 'Vorkath', 'Cerberus', 'Chambers of Xeric'])->random(),
                    'items' => [['id' => collect($lootIds)->random(), 'quantity' => random_int(1, 5)]],
                    'total_value' => random_int(100_000, 8_000_000),
                ],
            ]);
        }

        $this->command->info(sprintf('DemoContentSeeder: created %d feed events.', FeedEvent::count()));
    }
}
