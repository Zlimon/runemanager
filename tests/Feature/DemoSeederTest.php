<?php

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
use App\Models\Loot;
use App\Models\LootingBag;
use App\Models\Quest;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Support\CombatAchievements;
use App\Support\Diaries;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    // The writable Mongo collections aren't wrapped by RefreshDatabase.
    foreach ([Inventory::class, Bank::class, LootingBag::class, Quest::class, Loot::class] as $model) {
        $model::query()->delete();
    }
});

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
