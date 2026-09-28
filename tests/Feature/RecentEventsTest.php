<?php

use App\Helpers\SettingHelper;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\WomAchievement;
use App\Models\WomRecord;
use App\Services\Accounts\RecentEvent;
use App\Services\Accounts\RecentEvents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-09-28 06:00:00', 'UTC'));
    $this->account = Account::factory()->create();
});

/**
 * @return list<string>
 */
function logIds(Account $account, ?string $before = null, int $limit = 15): array
{
    return array_map(fn (RecentEvent $event): string => $event->id(), app(RecentEvents::class)->page($account, $before, $limit)['events']);
}

it('merges milestone feed events, dated achievements and records newest first', function () {
    SettingHelper::setSetting('feed_loot_min_value', 1_000_000, 'int');

    $quest = FeedEvent::factory()->for($this->account)->questComplete()->create(['occurred_at' => now()->subDays(1)]);
    FeedEvent::factory()->for($this->account)->create(['payload' => ['skill' => 'Hunter', 'level' => 41], 'occurred_at' => now()->subHours(2)]);
    FeedEvent::factory()->for($this->account)->death()->create(['occurred_at' => now()->subHours(3)]);
    FeedEvent::factory()->for($this->account)->lootDrop()->create(['payload' => ['source' => 'Zulrah', 'items' => [], 'total_value' => 500_000], 'occurred_at' => now()->subHours(4)]);
    $loot = FeedEvent::factory()->for($this->account)->lootDrop()->create(['payload' => ['source' => 'Vorkath', 'items' => [], 'total_value' => 3_000_000], 'occurred_at' => now()->subDays(3)]);

    $achievement = WomAchievement::factory()->for($this->account)->kills('vorkath', 500)->create(['achieved_at' => now()->subDays(2)]);
    WomAchievement::factory()->for($this->account)->undated()->create();
    WomAchievement::factory()->for($this->account)->legacy()->create(['achieved_at' => now()->subDays(2)]);

    $record = WomRecord::factory()->for($this->account)->create(['period' => 'week', 'metric' => 'slayer', 'achieved_at' => now()->subHours(12)]);
    WomRecord::factory()->for($this->account)->create(['period' => 'day', 'metric' => 'slayer', 'achieved_at' => now()->subHours(1)]);
    WomRecord::factory()->for($this->account)->create(['period' => 'week', 'metric' => 'ehp', 'value' => 50.5, 'achieved_at' => now()->subHours(1)]);

    // Another account's events never leak in.
    FeedEvent::factory()->questComplete()->create(['occurred_at' => now()]);

    expect(logIds($this->account))->toBe([
        "rec:{$record->id}",
        "feed:{$quest->id}",
        "ach:{$achievement->id}",
        "feed:{$loot->id}",
    ]);
});

it('does not repeat a 99 the feed already logged', function () {
    FeedEvent::factory()->for($this->account)->create(['payload' => ['skill' => 'Runecraft', 'level' => 99], 'occurred_at' => now()->subDays(5)]);
    WomAchievement::factory()->for($this->account)->level99('runecrafting')->create(['achieved_at' => now()->subDays(5)]);
    $cooking = WomAchievement::factory()->for($this->account)->level99('cooking')->create(['achieved_at' => now()->subDays(6)]);

    $events = app(RecentEvents::class)->page($this->account)['events'];

    expect(array_map(fn (RecentEvent $event): string => $event->source, $events))->toBe([RecentEvent::SOURCE_FEED, RecentEvent::SOURCE_ACHIEVEMENT])
        ->and($events[0]->title)->toBe('Mastered Runecraft.')
        ->and($events[1]->id())->toBe("ach:{$cooking->id}");
});

it('collapses records of one metric set at the same moment', function () {
    $moment = now()->subDays(2)->startOfMinute();
    $week = WomRecord::factory()->for($this->account)->kills('vorkath', 128)->create(['period' => 'week', 'achieved_at' => $moment]);
    WomRecord::factory()->for($this->account)->kills('vorkath', 202)->create(['period' => 'year', 'achieved_at' => $moment]);
    WomRecord::factory()->for($this->account)->kills('vorkath', 202)->create(['period' => 'month', 'achieved_at' => $moment]);
    $zulrah = WomRecord::factory()->for($this->account)->kills('zulrah', 40)->create(['period' => 'week', 'achieved_at' => $moment]);

    $events = app(RecentEvents::class)->page($this->account)['events'];

    expect($events)->toHaveCount(2)
        ->and($events[0]->id())->toBe("rec:{$zulrah->id}")
        ->and($events[1]->id())->toBe("rec:{$week->id}")
        ->and($events[1]->body)->toBe('I killed Vorkath 128 times in a single week, my best yet (also a new monthly and yearly best).')
        ->and($events[1]->icon)->toBe(['src' => '/images/boss/vorkath.png']);
});

it('pages with a stable cursor across equal timestamps and all three sources', function () {
    $moment = now()->subDay()->startOfMinute();

    FeedEvent::factory()->for($this->account)->questComplete()->count(5)->create(['occurred_at' => $moment]);
    FeedEvent::factory()->for($this->account)->pet()->count(2)->create(['occurred_at' => now()->subDays(2)]);
    WomAchievement::factory()->for($this->account)->count(4)->create(['achieved_at' => $moment]);
    foreach (['attack', 'strength', 'defence', 'magic'] as $skill) {
        foreach (['week', 'month'] as $period) {
            WomRecord::factory()->for($this->account)->create(['period' => $period, 'metric' => $skill, 'achieved_at' => $moment]);
        }
    }

    $everything = logIds($this->account, limit: 100);
    expect($everything)->toHaveCount(15);

    foreach ([1, 2, 3, 4, 7] as $limit) {
        $seen = [];
        $cursor = null;

        do {
            $page = app(RecentEvents::class)->page($this->account, $cursor, $limit);
            expect(count($page['events']))->toBeLessThanOrEqual($limit);
            array_push($seen, ...array_map(fn (RecentEvent $event): string => $event->id(), $page['events']));
            $cursor = $page['next_cursor'];
        } while ($cursor !== null);

        expect($seen)->toBe($everything);
    }
});

it('treats a malformed cursor as the first page', function (string $cursor) {
    FeedEvent::factory()->for($this->account)->questComplete()->count(3)->create();

    expect(logIds($this->account, $cursor, 2))->toBe(logIds($this->account, null, 2));
})->with([
    'not base64' => ['%%%'],
    'wrong shape' => [rtrim(strtr(base64_encode('hello'), '+/', '-_'), '=')],
    'unknown rank' => [rtrim(strtr(base64_encode('1700000000000:7:5'), '+/', '-_'), '=')],
]);

it('returns no cursor on the last page', function () {
    FeedEvent::factory()->for($this->account)->questComplete()->count(2)->create();

    expect(app(RecentEvents::class)->page($this->account, null, 2)['next_cursor'])->toBeNull()
        ->and(app(RecentEvents::class)->page($this->account, null, 1)['next_cursor'])->not->toBeNull();
});

it('survives malformed feed payloads', function () {
    FeedEvent::factory()->for($this->account)->questComplete()->create(['payload' => []]);
    FeedEvent::factory()->for($this->account)->collectionLog()->create(['payload' => ['item' => ['not', 'a', 'name']]]);
    FeedEvent::factory()->for($this->account)->create(['payload' => ['level' => 99]]);
    FeedEvent::factory()->for($this->account)->lootDrop()->create(['payload' => ['items' => 'broken', 'total_value' => 5_000_000]]);

    $events = app(RecentEvents::class)->page($this->account)['events'];

    expect($events)->toHaveCount(4)
        ->and(collect($events)->pluck('title')->sort()->values()->all())
        ->toBe(['Levelled up.', 'New collection log item.', 'Quest complete.', 'Received loot from a monster.']);
});

it('shapes feed rows with pin data and WOM rows without', function () {
    $pinned = FeedEvent::factory()->for($this->account)->questComplete()->pinned()->create(['payload' => ['quest' => 'Dragon Slayer I'], 'occurred_at' => now()->subHour()]);
    $achievement = WomAchievement::factory()->for($this->account)->level99('cooking')->approximate()->create(['achieved_at' => now()->subDays(3)]);

    $rows = RecentEventResource::collection(app(RecentEvents::class)->page($this->account)['events'])->resolve();

    expect($rows[0])->toBe([
        'id' => "feed:{$pinned->id}",
        'source' => 'feed',
        'type' => 'quest_complete',
        'title' => 'Quest complete: Dragon Slayer I.',
        'body' => 'I completed the quest Dragon Slayer I.',
        'occurred_at' => $pinned->occurred_at->toIso8601String(),
        'approximate' => false,
        'icon' => ['kind' => 'quests_tab', 'slug' => 'blue_quests_icon'],
        'feed_event_id' => $pinned->id,
        'pinned' => true,
        'screenshot_url' => null,
    ])->and($rows[1])->toBe([
        'id' => "ach:{$achievement->id}",
        'source' => 'wom_achievement',
        'type' => 'achievement',
        'title' => '99 Cooking',
        'body' => 'Around '.now()->subDays(3)->format('j F Y').", I've reached level 99 in the Cooking skill.",
        'occurred_at' => $achievement->achieved_at->toIso8601String(),
        'approximate' => true,
        'icon' => ['kind' => 'skill', 'slug' => 'cooking'],
    ]);
});
