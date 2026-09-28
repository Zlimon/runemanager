<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\AccountDiary;
use App\Models\AccountHiscore;
use App\Models\Announcement;
use App\Models\Bank;
use App\Models\CalendarEvent;
use App\Models\CalendarEventRsvp;
use App\Models\Equipment;
use App\Models\FeedEvent;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Support\CombatAchievements;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Fills a dev instance with realistic, offline (no live API) data across the
 * whole app: per-account stats/diaries/combat achievements/equipment/quests/
 * inventory/bank/loot, plus instance-wide announcements, calendar events and a
 * live feed. Idempotent — each piece is skipped per account (and topped up
 * instance-wide), so a re-run fills the gaps a partial run left behind.
 */
class DemoContentSeeder extends Seeder
{
    /** Extra synthetic accounts to flesh out the leaderboards. */
    private const TARGET_ACCOUNTS = 25;

    private const TARGET_ACTIVE_ANNOUNCEMENTS = 5;

    private const TARGET_EXPIRED_ANNOUNCEMENTS = 2;

    /** Announcements the plugin has already shown in-game to some accounts. */
    private const TARGET_ACKNOWLEDGED_ANNOUNCEMENTS = 2;

    private const TARGET_UPCOMING_EVENTS = 8;

    private const TARGET_PAST_EVENTS = 4;

    /** Upcoming events painted in one of the instance's highlight colours. */
    private const TARGET_HIGHLIGHTED_EVENTS = 3;

    public function run(): void
    {
        // Every random choice below runs through faker so one fixed seed makes
        // the whole demo reproducible.
        fake()->seed(DatabaseSeeder::FAKER_SEED);

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

        // Last: it settles the account set against the instance mode and fills
        // in what only the plugin ever pushes, so it needs everyone who is
        // going to exist to exist already.
        $this->call(PresenceSeeder::class);
    }

    /**
     * Create extra factory accounts so the leaderboards aren't sparse.
     *
     * @param  Collection<int, User>  $users
     */
    private function topUpAccounts(Collection $users): void
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
            // account cards / top-accounts / overall board have data. Account is
            // totally guarded, so this has to go through forceFill.
            $account->forceFill([
                'rank' => $overall['rank'],
                'level' => $overall['level'],
                'xp' => $overall['xp'],
            ])->save();
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
        if (fake()->numberBetween(0, 2) === 0 && ! LootingBag::where('account_id', $account->id)->exists()) {
            LootingBag::factory()->create(['account_id' => $account->id]);
        }

        if (! Loot::where('account_id', $account->id)->exists()) {
            Loot::factory()->count(fake()->numberBetween(4, 14))->create(['account_id' => $account->id]);
        }
    }

    /**
     * Realistic, difficulty-decreasing per-tier counts + matching points.
     *
     * @return array{points: int, tiers: array<string, int>}
     */
    private function combatAchievementAttributes(): array
    {
        $tiers = [];
        $points = 0;
        $pointValue = ['easy' => 1, 'medium' => 2, 'hard' => 3, 'elite' => 4, 'master' => 5, 'grandmaster' => 6];

        foreach (CombatAchievements::TIER_TOTALS as $tier => $total) {
            $done = fake()->numberBetween(0, $total);
            $tiers[$tier] = $done;
            $points += $done * $pointValue[$tier];
        }

        return ['points' => $points, 'tiers' => $tiers];
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedAnnouncements(Collection $users): void
    {
        $active = self::TARGET_ACTIVE_ANNOUNCEMENTS - Announcement::active()->count();
        $expired = self::TARGET_EXPIRED_ANNOUNCEMENTS - (Announcement::count() - Announcement::active()->count());

        if ($active > 0) {
            Announcement::factory()->count($active)->create(['user_id' => $users->random()->id]);
        }

        if ($expired > 0) {
            Announcement::factory()->expired()->count($expired)->create(['user_id' => $users->random()->id]);
        }

        if ($active > 0 || $expired > 0) {
            $this->command->info(sprintf('DemoContentSeeder: created %d announcements.', max($active, 0) + max($expired, 0)));
        }

        $this->seedAcknowledgements();
    }

    /**
     * Accounts that have already seen an announcement in-game, as the plugin's
     * acknowledge call records them — only on the newest couple, so the rest
     * still read as unseen.
     */
    private function seedAcknowledgements(): void
    {
        $accountIds = Account::query()->whereNotNull('user_id')->pluck('id');

        if ($accountIds->isEmpty()) {
            return;
        }

        foreach (Announcement::active()->take(self::TARGET_ACKNOWLEDGED_ANNOUNCEMENTS)->get() as $announcement) {
            if ($announcement->acknowledgedBy()->exists()) {
                continue;
            }

            $announcement->acknowledgedBy()->attach(
                $accountIds->random(min(fake()->numberBetween(5, 12), $accountIds->count()))->all(),
            );
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedCalendar(Collection $users): void
    {
        $missingUpcoming = self::TARGET_UPCOMING_EVENTS - CalendarEvent::upcoming()->count();
        $missingPast = self::TARGET_PAST_EVENTS - CalendarEvent::past()->count();

        if ($missingUpcoming > 0) {
            // A couple of them painted in a colour the instance highlights, so
            // the calendar's "important" styling has something to apply to.
            $highlighted = min(self::TARGET_HIGHLIGHTED_EVENTS, $missingUpcoming);

            CalendarEvent::factory()->count($missingUpcoming - $highlighted)->create(['user_id' => $users->random()->id]);
            CalendarEvent::factory()->highlighted()->count($highlighted)->create(['user_id' => $users->random()->id]);
        }

        if ($missingPast > 0) {
            CalendarEvent::factory()->past()->count($missingPast)->create(['user_id' => $users->random()->id]);
        }

        if ($missingUpcoming > 0 || $missingPast > 0) {
            $this->command->info(sprintf(
                'DemoContentSeeder: created %d calendar events.',
                max($missingUpcoming, 0) + max($missingPast, 0),
            ));
        }

        $this->seedRsvps();
    }

    /**
     * Attendance on the events still to come. An RSVP is one account signing
     * up, carrying the user who did it so the calendar can tell the viewer
     * whether they are already on the list.
     */
    private function seedRsvps(): void
    {
        // A member signs up once per event however many accounts they own —
        // `calendar_event_rsvps` is unique on (event, user) as well as
        // (event, account) — so only one account per user is a candidate.
        $accounts = Account::query()->whereNotNull('user_id')->get()->unique('user_id')->values();

        if ($accounts->isEmpty()) {
            return;
        }

        $created = 0;

        foreach (CalendarEvent::upcoming()->get() as $event) {
            // Per event, so a run that adds events fills only those.
            if ($event->rsvps()->exists()) {
                continue;
            }

            $attending = $accounts->random(min(fake()->numberBetween(2, 8), $accounts->count()));

            foreach ($attending as $account) {
                CalendarEventRsvp::factory()->create([
                    'calendar_event_id' => $event->id,
                    'account_id' => $account->id,
                    'user_id' => $account->user_id,
                ]);
                $created++;
            }
        }

        if ($created > 0) {
            $this->command->info(sprintf('DemoContentSeeder: created %d event RSVPs.', $created));
        }
    }

    /**
     * @param  Collection<int, Account>  $accounts
     */
    private function seedFeed(Collection $accounts): void
    {
        $before = FeedEvent::count();

        foreach ($accounts->values() as $index => $account) {
            // Per account, not per table: an account added by a later run still
            // gets a feed without wiping or duplicating everyone else's.
            if (FeedEvent::where('account_id', $account->id)->exists()) {
                continue;
            }

            $attributes = ['account_id' => $account->id];

            // The everyday events, on everyone, with where they happened for
            // the event map. DemoEventPositionsSeeder places most of the rest.
            FeedEvent::factory()->positioned()->count(fake()->numberBetween(1, 3))->create($attributes);
            FeedEvent::factory()->questComplete()->positioned()->create($attributes);
            FeedEvent::factory()->combatAchievement()->positioned()->create($attributes);
            FeedEvent::factory()->lootDrop()->positioned()->create($attributes);

            $this->seedRareFeedEvent($index, $attributes);

            // The account's achievement gallery, which is built around each
            // pinned event's screenshot.
            FeedEvent::factory()->lootDrop()->pinned()->withScreenshot()->create($attributes);
            FeedEvent::factory()->combatAchievement()->pinned()->withScreenshot()->create($attributes);
        }

        $this->command->info(sprintf('DemoContentSeeder: created %d feed events.', FeedEvent::count() - $before));
    }

    /**
     * One of the rarer event types, handed out in rotation rather than at
     * random: every type is then certain to appear somewhere in the feed (so no
     * type filter comes back empty) without every account owning a pet and
     * dying exactly once.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function seedRareFeedEvent(int $index, array $attributes): void
    {
        $factory = FeedEvent::factory();

        $event = match ($index % 4) {
            0 => $factory->collectionLog(),
            1 => $factory->pet(),
            2 => $factory->death(),
            default => $factory->reward(),
        };

        $event->create($attributes);
    }
}
