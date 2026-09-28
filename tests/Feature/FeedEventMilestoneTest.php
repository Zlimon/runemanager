<?php

use App\Helpers\SettingHelper;
use App\Http\Resources\FeedEventResource;
use App\Models\Account;
use App\Models\FeedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('classifies level-ups: 99 and every tenth level are milestones', function (int $level, bool $milestone) {
    expect(FeedEvent::classify(FeedEvent::TYPE_LEVEL_UP, ['skill' => 'Hunter', 'level' => $level]))
        ->toBe(['milestone' => $milestone, 'value' => null]);
})->with([
    'level 99' => [99, true],
    'level 40' => [40, true],
    'level 90' => [90, true],
    'level 41' => [41, false],
    'level 98' => [98, false],
    'level 1' => [1, false],
]);

it('classifies the other event types', function (string $type, array $payload, array $expected) {
    expect(FeedEvent::classify($type, $payload))->toBe($expected);
})->with([
    'quest' => [FeedEvent::TYPE_QUEST_COMPLETE, ['quest' => 'Dragon Slayer I'], ['milestone' => true, 'value' => null]],
    'combat task' => [FeedEvent::TYPE_COMBAT_ACHIEVEMENT, ['task' => 'Peach Conjurer', 'tier' => 'hard'], ['milestone' => true, 'value' => null]],
    'collection log' => [FeedEvent::TYPE_COLLECTION_LOG, ['item' => 'Twisted bow'], ['milestone' => true, 'value' => null]],
    'pet' => [FeedEvent::TYPE_PET, [], ['milestone' => true, 'value' => null]],
    'loot' => [FeedEvent::TYPE_LOOT_DROP, ['source' => 'Zulrah', 'items' => [], 'total_value' => 1_250_000], ['milestone' => false, 'value' => 1_250_000]],
    'death' => [FeedEvent::TYPE_DEATH, [], ['milestone' => false, 'value' => null]],
    'reward' => [FeedEvent::TYPE_REWARD, ['source' => 'Barrows'], ['milestone' => false, 'value' => null]],
]);

it('fills milestone and value when an event is saved', function () {
    $levelUp = FeedEvent::factory()->create(['payload' => ['skill' => 'Cooking', 'level' => 99]]);
    $loot = FeedEvent::factory()->lootDrop()->create();
    $death = FeedEvent::factory()->death()->create();

    expect($levelUp->fresh()->milestone)->toBeTrue()
        ->and($loot->fresh()->milestone)->toBeFalse()
        ->and($loot->fresh()->value)->toBe($loot->payload['total_value'])
        ->and($death->fresh()->milestone)->toBeFalse();

    $levelUp->update(['payload' => ['skill' => 'Cooking', 'level' => 41]]);

    expect($levelUp->fresh()->milestone)->toBeFalse();
});

it('selects milestones and loot at the loot threshold read at query time', function () {
    $account = Account::factory()->create();
    $milestone = FeedEvent::factory()->for($account)->questComplete()->create();
    FeedEvent::factory()->for($account)->create(['payload' => ['skill' => 'Hunter', 'level' => 41]]);
    FeedEvent::factory()->for($account)->death()->create();
    $small = FeedEvent::factory()->for($account)->lootDrop()->create(['payload' => ['source' => 'Zulrah', 'items' => [], 'total_value' => 200_000]]);
    $big = FeedEvent::factory()->for($account)->lootDrop()->create(['payload' => ['source' => 'Vorkath', 'items' => [], 'total_value' => 2_000_000]]);

    SettingHelper::setSetting('feed_loot_min_value', 1_000_000, 'int');

    expect(FeedEvent::query()->logMilestones()->pluck('id')->sort()->values()->all())
        ->toBe([$milestone->id, $big->id])
        ->and($big->isLogMilestone())->toBeTrue()
        ->and($small->isLogMilestone())->toBeFalse();

    SettingHelper::setSetting('feed_loot_min_value', 100_000, 'int');

    expect(FeedEvent::query()->logMilestones()->pluck('id')->sort()->values()->all())
        ->toBe([$milestone->id, $small->id, $big->id])
        ->and($small->isLogMilestone())->toBeTrue();
});

it('exposes the milestone flag on the feed wire shape', function () {
    $event = FeedEvent::factory()->create(['payload' => ['skill' => 'Hunter', 'level' => 50]]);

    expect((new FeedEventResource($event))->resolve()['milestone'])->toBeTrue();
});

it('backfills events recorded before the columns existed exactly as classify() would', function () {
    $account = Account::factory()->create();
    FeedEvent::factory()->for($account)->create(['payload' => ['skill' => 'Cooking', 'level' => 99]]);
    FeedEvent::factory()->for($account)->create(['payload' => ['skill' => 'Hunter', 'level' => 40]]);
    FeedEvent::factory()->for($account)->create(['payload' => ['skill' => 'Hunter', 'level' => 41]]);
    FeedEvent::factory()->for($account)->lootDrop()->count(3)->create();
    FeedEvent::factory()->for($account)->questComplete()->create();
    FeedEvent::factory()->for($account)->combatAchievement()->create();
    FeedEvent::factory()->for($account)->collectionLog()->create();
    FeedEvent::factory()->for($account)->pet()->create();
    FeedEvent::factory()->for($account)->death()->create();
    FeedEvent::factory()->for($account)->reward()->create();

    // As the rows stood right after the ALTER: defaults only.
    DB::table('feed_events')->update(['milestone' => false, 'value' => null]);

    (require database_path('migrations/2026_09_28_061717_backfill_feed_event_milestone_columns.php'))->up();

    $rows = DB::table('feed_events')->get();
    expect($rows)->toHaveCount(12);

    foreach ($rows as $row) {
        expect(['milestone' => (bool) $row->milestone, 'value' => $row->value === null ? null : (int) $row->value])
            ->toBe(FeedEvent::classify($row->type, json_decode($row->payload, true)));
    }
});
