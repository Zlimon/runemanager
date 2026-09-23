<?php

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
use App\Services\Loot\LootLeaderboard;
use App\Support\CombatAchievements;
use App\Support\Diaries;
use App\Support\Instance;
use Database\Factories\CollectionLogFactory;
use Database\Factories\GroupBankFactory;
use Database\Factories\ResourcePackFactory;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\DemoSnapshotsSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\Support\ItemPool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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
