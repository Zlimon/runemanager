<?php

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Models\AccountDiary;
use App\Models\AccountHiscore;
use App\Models\Announcement;
use App\Models\Bank;
use App\Models\CalendarEvent;
use App\Models\CalendarEventRsvp;
use App\Models\CollectionLog;
use App\Models\Equipment;
use App\Models\FeedEvent;
use App\Models\GroupBank;
use App\Models\Inventory;
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\ResourcePack;
use App\Models\Setting;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Models\WomAchievement;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use App\Services\Loot\LootLeaderboard;
use App\Services\WiseOldMan\WomMetrics;
use App\Support\CombatAchievements;
use App\Support\Diaries;
use App\Support\Instance;
use Database\Factories\CollectionLogFactory;
use Database\Factories\GroupBankFactory;
use Database\Factories\ResourcePackFactory;
use Database\Seeders\AccountSeeder;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\DemoSnapshotsSeeder;
use Database\Seeders\DemoWomSeeder;
use Database\Seeders\LiveWomSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\Support\ItemPool;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

afterEach(function () {
    // The writable Mongo collections aren't wrapped by RefreshDatabase.
    foreach ([Inventory::class, Bank::class, LootingBag::class, Quest::class, Loot::class] as $model) {
        $model::query()->delete();
    }
});

/**
 * Every `FeedEvent::TYPE_*` constant, read reflectively so that adding a ninth
 * event type makes the coverage case below fail until the demo seed emits it.
 *
 * @return list<string>
 */
function feedEventTypes(): array
{
    return array_values(array_filter(
        (new ReflectionClass(FeedEvent::class))->getConstants(),
        fn (string $key): bool => str_starts_with($key, 'TYPE_'),
        ARRAY_FILTER_USE_KEY,
    ));
}

it('builds a full hiscore entries snapshot', function () {
    $hiscore = AccountHiscore::factory()->create();

    $skills = $hiscore->entries['skills'];
    expect($skills)->toHaveKey('overall')
        ->and($skills)->toHaveKeys(['attack', 'slayer', 'runecraft', 'sailing'])
        ->and($skills['overall']['level'])->toBeGreaterThan(0)
        ->and($hiscore->entries['activities'])->toHaveKey('clue_scrolls_all');
});

it('builds diary completion across the canonical area/tier set', function () {
    $diary = AccountDiary::factory()->create();

    expect($diary->diaries)->toHaveCount(count(Diaries::AREAS))
        ->and($diary->diaries['Varrock'])->toHaveKeys(Diaries::TIERS)
        ->and($diary->diaries['Varrock']['Easy'])->toBeBool();
});

it('builds combat achievement counts over the canonical tiers', function () {
    $ca = AccountCombatAchievement::factory()->create();

    expect($ca->tiers)->toHaveKeys(CombatAchievements::tiers())
        ->and($ca->tiers['easy'])->toBeInt();
});

it('builds feed events of each type', function () {
    expect(FeedEvent::factory()->create()->type)->toBe(FeedEvent::TYPE_LEVEL_UP)
        ->and(FeedEvent::factory()->lootDrop()->create()->type)->toBe(FeedEvent::TYPE_LOOT_DROP)
        ->and(FeedEvent::factory()->questComplete()->create()->type)->toBe(FeedEvent::TYPE_QUEST_COMPLETE)
        ->and(FeedEvent::factory()->combatAchievement()->create()->type)->toBe(FeedEvent::TYPE_COMBAT_ACHIEVEMENT);

    expect(FeedEvent::factory()->combatAchievement()->create()->payload)->toHaveKeys(['task', 'tier']);
});

it('builds equipment and username history', function () {
    expect(Equipment::factory()->create()->account_id)->toBeInt();
    expect(UsernameHistory::factory()->create()->new_username)->toBeString();
});

it('builds Mongo-backed snapshots with real item ids', function () {
    $account = Account::factory()->create();

    $inventory = Inventory::factory()->create(['account_id' => $account->id]);
    expect($inventory->inventory)->toHaveCount(28)
        ->and($inventory->inventory[0])->toHaveCount(2);

    $bank = Bank::factory()->create(['account_id' => $account->id]);
    expect($bank->bank)->toBeArray()->not->toBeEmpty()
        ->and($bank->bank[0][0])->toHaveCount(2); // first tab, first slot = [id, qty]

    $bag = LootingBag::factory()->create(['account_id' => $account->id]);
    expect($bag->looting_bag)->toBeArray();

    $quest = Quest::factory()->create(['account_id' => $account->id]);
    expect($quest->quests[0][0])->toBeString()
        ->and($quest->quests[0][1])->toBeInt();

    $loot = Loot::factory()->create(['account_id' => $account->id]);
    expect($loot->items[0])->toHaveKeys(['id', 'quantity'])
        ->and($loot->source)->toBeString();
});

it('seeder populates accounts and instance-wide content', function () {
    User::factory()->count(3)->withPersonalTeam()->create();

    $this->seed(DemoContentSeeder::class);

    $account = Account::query()->first();
    expect(Account::count())->toBeGreaterThanOrEqual(25)
        ->and($account->hiscore)->not->toBeNull()
        ->and($account->diary)->not->toBeNull()
        ->and($account->combatAchievement)->not->toBeNull()
        ->and($account->level)->toBeGreaterThan(0);

    expect(FeedEvent::count())->toBeGreaterThan(0)
        ->and(Announcement::count())->toBeGreaterThan(0)
        ->and(CalendarEvent::count())->toBeGreaterThan(0)
        ->and(Loot::count())->toBeGreaterThan(0);
});

it('never lets Account be mass-assignable again', function () {
    // Every account write in the app goes through forceFill because the model
    // carries no #[Fillable]. A seeder that "fixed" its MassAssignmentException
    // by unguarding the model would silently widen the plugin endpoints' write
    // surface, so pin the guarding itself rather than the seeder's behaviour.
    expect((new Account)->totallyGuarded())->toBeTrue();
});

it('builds a collection log in the shape the TempleOSRS sync writes', function () {
    $account = Account::factory()->create();

    $log = CollectionLogFactory::new()->create(['account_id' => $account->id]);

    expect($log->items)->toBeArray()->not->toBeEmpty()
        ->and($log->obtained)->toBeGreaterThan(0)
        ->and($log->total)->toBeGreaterThan($log->obtained)
        ->and($log->categories_available)->toBeGreaterThanOrEqual($log->categories_finished)
        ->and($log->fetched_at)->not->toBeNull();

    $slug = array_key_first($log->items);
    expect($log->items[$slug][0])->toHaveKeys(['id', 'count', 'date'])
        ->and($log->items[$slug][0]['id'])->toBeInt();

    // The panel falls back to the obtained-only set when TempleOSRS' structure
    // can't be fetched, which is the offline demo path.
    expect($account->fresh()->collectionLog)->not->toBeNull();
});

it('builds a completionist collection log that tops the leaderboard', function () {
    $partial = CollectionLogFactory::new()->create();
    $complete = CollectionLogFactory::new()->completionist()->create();

    expect($complete->obtained)->toBeGreaterThan($partial->obtained)
        ->and($complete->categories_finished)->toBe(count($complete->items));
});

it('builds a group bank as a flat id/quantity list', function () {
    $bank = GroupBankFactory::new()->create();

    expect($bank->items)->toBeArray()->not->toBeEmpty()
        ->and($bank->items[0])->toHaveCount(2)
        ->and($bank->items[0][0])->toBeInt()
        ->and($bank->items[0][1])->toBeGreaterThan(0);
});

it('builds distinct resource pack rows including the vanilla baseline', function () {
    $vanilla = ResourcePackFactory::new()->vanilla()->create();
    $packs = ResourcePackFactory::new()->count(3)->create();

    expect($vanilla->isVanilla())->toBeTrue()
        ->and($vanilla->name)->toBe(ResourcePack::VANILLA)
        ->and($packs->pluck('name')->unique())->toHaveCount(3)
        ->and($packs->pluck('name'))->not->toContain(ResourcePack::VANILLA);

    // The picker and Admin → Packs read these rows; the sprites themselves are
    // gitignored and downloaded at runtime, so a row without files is expected.
    expect(ResourcePack::effectiveFor(null)?->id)->toBe($vanilla->id);
});

it('builds loot for every section of the loot directory', function () {
    $account = Account::factory()->create();

    foreach (array_keys(LootLeaderboard::CATEGORIES) as $type) {
        $loot = Loot::factory()->ofType($type)->create(['account_id' => $account->id]);

        expect($loot->type)->toBe($type)
            ->and($loot->source)->toBeString()->not->toBeEmpty();
    }

    expect(Loot::where('account_id', $account->id)->count())
        ->toBe(count(LootLeaderboard::CATEGORIES));
});

it('samples the item catalogue once and reuses the same pool', function () {
    ItemPool::forget();

    $first = ItemPool::ids(ItemPool::SIZE);
    $second = ItemPool::ids(ItemPool::SIZE);

    expect($first)->not->toBeEmpty()
        ->and($second)->toEqualCanonicalizing($first)
        ->and(ItemPool::isEmpty())->toBeFalse();

    // Equipment slots are sampled per slot, once for the whole run.
    expect(ItemPool::slotId('weapon'))->toBeInt()
        ->and(ItemPool::slotId('weapon'))->toBeInt();

    expect(Equipment::factory()->create()->weapon)->toBeInt();
});

it('degrades to empty snapshots when the item catalogue is unavailable', function () {
    $database = config('database.connections.mongodb-static.database');

    try {
        // An unpopulated catalogue, which is what an environment where
        // `mongodb-static-populate` never ran actually looks like.
        config(['database.connections.mongodb-static.database' => 'runemanager_no_such_catalogue']);
        DB::purge('mongodb-static');
        ItemPool::forget();

        expect(ItemPool::isEmpty())->toBeTrue()
            ->and(ItemPool::ids(10))->toBe([])
            ->and(ItemPool::id())->toBe(0)
            ->and(ItemPool::slotId('weapon'))->toBeNull();

        // Factories must still produce documents, just empty ones.
        $account = Account::factory()->create();
        expect(Inventory::factory()->create(['account_id' => $account->id])->inventory)->toHaveCount(28)
            ->and(Equipment::factory()->create(['account_id' => $account->id])->weapon)->toBeNull()
            ->and(Loot::factory()->create(['account_id' => $account->id])->items)->toBe([]);
    } finally {
        config(['database.connections.mongodb-static.database' => $database]);
        DB::purge('mongodb-static');
        ItemPool::forget();
    }
});

it('seeds an instance that is configured', function () {
    // TestCase marks every test's instance as configured so the setup gate
    // doesn't redirect it; start from the genuinely fresh state instead.
    Setting::query()->delete();
    Setting::flushMemo();
    expect(Instance::isConfigured())->toBeFalse();

    $this->seed(SettingsSeeder::class);

    // Without this the middleware renders SetupInProgress in place of every
    // page, however much data sits behind it.
    expect(Instance::isConfigured())->toBeTrue()
        ->and(Instance::MODES)->toContain(Instance::mode());
});

it('seeds a demo instance with every surface populated', function () {
    User::factory()->count(3)->withPersonalTeam()->create();

    $this->seed(SettingsSeeder::class);
    $this->seed(DemoContentSeeder::class);
    $this->seed(DemoSnapshotsSeeder::class);

    // Live Feed: every type the UI can render, plus the pinned events the
    // account achievement gallery is built from.
    expect(FeedEvent::query()->distinct()->pluck('type')->all())
        ->toEqualCanonicalizing(feedEventTypes());
    expect(FeedEvent::pinned()->exists())->toBeTrue();

    // Presence: the Live Map and the status orbs.
    expect(Account::onMap()->exists())->toBeTrue();
    expect(Account::query()->whereNotNull('vitals_updated_at')->get()
        ->contains(fn (Account $account): bool => $account->vitalsPayload() !== null))->toBeTrue();

    // The Mongo-heavy and calendar surfaces the audit found empty.
    expect(CollectionLog::count())->toBeGreaterThan(0)
        ->and(CalendarEventRsvp::count())->toBeGreaterThan(0);

    if (Instance::isGroup()) {
        expect(GroupBank::count())->toBe(1);
    }

    // The theme picker and Admin → Packs, plus the override hierarchy:
    // personal pick → instance default → Vanilla → no pack at all.
    expect(ResourcePack::query()->where('name', ResourcePack::VANILLA)->exists())->toBeTrue()
        ->and(ResourcePack::count())->toBeGreaterThan(1)
        ->and(Instance::defaultResourcePackId())->not->toBeNull()
        ->and(User::query()->whereNotNull('resource_pack_id')->exists())->toBeTrue()
        ->and(User::query()->where('disable_resource_pack', true)->exists())->toBeTrue();
});

it('seeds the group bank only in GROUP mode', function () {
    Account::factory()->create();

    $this->seed(DemoSnapshotsSeeder::class);
    expect(GroupBank::count())->toBe(0);

    // The group bank page 404s outside GROUP mode, so the document only has a
    // reader — and only belongs in the demo — once the mode says so.
    Setting::set('instance_mode', Instance::MODE_GROUP);
    expect(Instance::isGroup())->toBeTrue();

    $this->seed(DemoSnapshotsSeeder::class);
    expect(GroupBank::count())->toBe(1);

    $bank = GroupBank::query()->first();
    expect($bank->items)->toBeArray()->not->toBeEmpty();
});

it('fills gaps rather than duplicating them on a second run', function () {
    User::factory()->count(3)->withPersonalTeam()->create();

    $this->seed(SettingsSeeder::class);
    $this->seed(DemoContentSeeder::class);
    $this->seed(DemoSnapshotsSeeder::class);

    $logs = CollectionLog::count();
    $packs = ResourcePack::count();

    // `collection_log` is unique on account_id and the pack rows drive a picker
    // that must not list the same pack twice, so a re-run has to be a no-op.
    $this->seed(DemoSnapshotsSeeder::class);

    expect(CollectionLog::count())->toBe($logs)
        ->and(ResourcePack::count())->toBe($packs);
});

it('seeds real Wise Old Man accounts with their account types', function () {
    $this->seed(UserSeeder::class);
    $this->seed(AccountSeeder::class);

    // These names are live, WOM-tracked players, so a WOM sync of the demo
    // instance has real history to pull; the types match what WOM reports.
    $types = Account::query()->pluck('account_type', 'username')
        ->map(fn (AccountTypesEnum|string $type): string => $type instanceof AccountTypesEnum ? $type->value : $type);

    expect($types)->toHaveCount(14)
        ->and($types['Habski'])->toBe(AccountTypesEnum::IRONMAN->value)
        ->and($types['Bartheez'])->toBe(AccountTypesEnum::IRONMAN->value)
        ->and($types['L apras'])->toBe(AccountTypesEnum::ULTIMATE_IRONMAN->value)
        ->and($types['Ju1es'])->toBe(AccountTypesEnum::HARDCORE_IRONMAN->value)
        ->and($types['Meekss'])->toBe(AccountTypesEnum::NORMAL->value);

    // A second run fills gaps instead of duplicating usernames.
    $this->seed(AccountSeeder::class);
    expect(Account::count())->toBe(14);
});

it('enables Wise Old Man with a public progress page', function () {
    $this->seed(SettingsSeeder::class);

    expect(Instance::womEnabled())->toBeTrue()
        ->and(Instance::womProgressPublic())->toBeTrue()
        ->and(Instance::womActiveWithinDays())->toBe(30);
});

it('seeds a Wise Old Man mix of active, inactive, hidden and unknown accounts with a year of history', function () {
    Http::preventStrayRequests();
    Account::factory()->count(14)->create();

    $this->seed(DemoWomSeeder::class);

    expect(WomPlayer::count())->toBe(14)
        ->and(WomPlayer::active()->count())->toBeGreaterThanOrEqual(8)
        ->and(WomPlayer::linked()->where('last_changed_at', '<', now()->subDays(30))->count())->toBeGreaterThanOrEqual(1)
        ->and(WomPlayer::whereNull('wom_player_id')->whereNotNull('not_found_at')->count())->toBe(1)
        ->and(WomPlayer::where('show_on_progress', false)->count())->toBe(1)
        ->and(WomPlayer::where('tracking_opt_in', true)->count())->toBe(2);

    // Each linked account ends its series on exactly what its row says.
    $player = WomPlayer::active()->orderBy('account_id')->first();
    $series = WomDailySnapshot::where('account_id', $player->account_id)->orderBy('date')->get();

    expect($series)->toHaveCount(DemoWomSeeder::DAYS)
        ->and($series->last()->date)->toBe(now('UTC')->toDateString())
        ->and($series->last()->exp)->toBe($player->exp)
        ->and($series->first()->exp)->toBeLessThanOrEqual($player->exp)
        ->and($series->pluck('exp')->sort()->values()->all())->toBe($series->pluck('exp')->all());

    // An inactive account is flat since its last change.
    $inactive = WomPlayer::linked()->where('last_changed_at', '<', now()->subDays(30))->first();
    $recent = WomDailySnapshot::where('account_id', $inactive->account_id)
        ->where('date', '>=', now('UTC')->subDays(30)->toDateString())
        ->pluck('exp')->unique();

    expect($recent->all())->toBe([$inactive->exp])
        ->and(WomDailySnapshot::whereIn('account_id', WomPlayer::whereNull('wom_player_id')->pluck('account_id'))->count())->toBe(0);
});

it('fills Wise Old Man gaps rather than duplicating them on a second run', function () {
    Account::factory()->count(4)->create();
    $this->seed(DemoWomSeeder::class);

    $players = WomPlayer::count();
    $points = WomDailySnapshot::count();
    $first = WomPlayer::linked()->orderBy('account_id')->first();
    WomDailySnapshot::where('account_id', $first->account_id)->limit(10)->get()->each->delete();

    $this->seed(DemoWomSeeder::class);

    expect(WomPlayer::count())->toBe($players)
        ->and(WomDailySnapshot::count())->toBe($points);
});

it('replaces demo Wise Old Man data with live data using GETs only', function () {
    Http::preventStrayRequests();
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response(womFixture('snapshots-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/achievements' => Http::response(womFixture('achievements-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/records' => Http::response(womFixture('records-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez' => Http::response(womFixture('player-bartheez')),
        'api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404),
    ]);

    // Opted in and far behind: a real sync would POST here, the seeder must not.
    $bartheez = Account::factory()->create(['username' => 'Bartheez', 'xp' => 900_000_000, 'last_seen_at' => now()]);
    $unknown = Account::factory()->create(['username' => 'Nobody Here']);
    Account::factory()->create();
    $this->seed(DemoWomSeeder::class);
    WomPlayer::where('account_id', $bartheez->id)->update(['tracking_opt_in' => true, 'update_requested_at' => null]);
    $unknownPoints = WomDailySnapshot::where('account_id', $unknown->id)->count();

    $this->seed(LiveWomSeeder::class);

    expect(Http::recorded(fn (Request $request) => $request->method() !== 'GET'))->toBeEmpty();

    $player = WomPlayer::where('account_id', $bartheez->id)->first();
    expect($player->wom_player_id)->toBe(315771)
        ->and($player->exp)->toBe(832820305)
        ->and(WomDailySnapshot::where('account_id', $bartheez->id)->pluck('date')->sort()->values()->all())
        ->toBe(['2026-09-25', '2026-09-26'])
        ->and($unknownPoints)->toBe(DemoWomSeeder::DAYS)
        ->and(WomDailySnapshot::where('account_id', $unknown->id)->count())->toBe($unknownPoints)
        // WOM's own achievements and records replace the synthetic ones.
        ->and(WomAchievement::where('account_id', $bartheez->id)->pluck('name')->sort()->values()->all())
        ->toBe(collect(womFixture('achievements-bartheez'))->pluck('name')->sort()->values()->all())
        ->and(WomRecord::where('account_id', $bartheez->id)->where('metric', 'zulrah')->where('period', 'week')->count())->toBe(1)
        ->and($player->milestones_synced_at)->not->toBeNull();
});

it('draws each demo Wise Old Man history from the seeded hiscores, with milestones', function () {
    Http::preventStrayRequests();
    $accounts = Account::factory()->count(4)->create();
    $accounts->each(fn (Account $account) => AccountHiscore::factory()->create(['account_id' => $account->id]));

    $this->seed(DemoWomSeeder::class);

    $player = WomPlayer::active()->orderBy('account_id')->first();
    $entries = AccountHiscore::where('account_id', $player->account_id)->first()->entries;
    $series = WomDailySnapshot::where('account_id', $player->account_id)->orderBy('date')->get();
    $latest = $series->last();

    $skills = collect($entries['skills'])->except('overall')
        ->mapWithKeys(fn (array $entry, string $slug) => [WomMetrics::fromLocalSlug($slug) => $entry['xp']])->all();
    $bosses = collect($entries['activities'])
        ->filter(fn (array $entry, string $slug) => WomMetrics::group($slug) === 'boss' && $entry['score'] >= DemoWomSeeder::MIN_BOSS_KILLS)
        ->map(fn (array $entry) => $entry['score'])->all();
    $clues = collect($entries['activities'])
        ->filter(fn (array $entry, string $slug) => str_starts_with($slug, 'clue_scrolls_'))
        ->map(fn (array $entry) => $entry['score'])->all();

    // The newest document is the hiscores, under WOM's metric names.
    expect($latest->skills)->toEqual($skills)
        ->and($latest->skills)->toHaveKey('runecrafting')
        ->and($latest->bosses)->toEqual($bosses)
        ->and($latest->activities)->toEqual($clues)
        ->and($latest->exp)->toBe($entries['skills']['overall']['xp'])
        ->and($player->exp)->toBe($latest->exp);

    // Every day's total is the sum of its skills, and no metric ever grows backwards.
    foreach ($series as $i => $day) {
        expect($day->exp)->toBe(array_sum($day->skills));

        if ($i > 0) {
            foreach ($series[$i - 1]->skills as $skill => $xp) {
                expect($day->skills[$skill])->toBeGreaterThanOrEqual($xp);
            }
        }
    }

    $achievements = WomAchievement::where('account_id', $player->account_id)->get();

    expect($achievements->where('legacy', true))->toHaveCount(1)
        ->and($achievements->whereNull('achieved_at'))->toHaveCount(3)
        ->and($achievements->where('measure', 'kills')->count())->toBeGreaterThan(0)
        ->and(WomRecord::where('account_id', $player->account_id)->whereIn('period', ['day', 'week', 'month', 'year'])->count())->toBeGreaterThan(0)
        ->and(WomRecord::where('account_id', $player->account_id)->where('achieved_at', '>', now())->count())->toBe(0)
        ->and($player->milestones_synced_at)->not->toBeNull();

    // A second run leaves the milestones alone.
    $this->seed(DemoWomSeeder::class);

    expect(WomAchievement::where('account_id', $player->account_id)->count())->toBe($achievements->count());
});
