<?php

use App\Models\FeedEvent;
use App\Models\WomAchievement;
use App\Services\Accounts\AdventurersLogCopy;
use Illuminate\Support\Carbon;

function feedCopy(string $type, array $payload, ?string $topItem = null): array
{
    return AdventurersLogCopy::forFeedEvent(new FeedEvent(['type' => $type, 'payload' => $payload]), $topItem);
}

function achievementCopy(array $attributes): array
{
    return AdventurersLogCopy::forAchievement(new WomAchievement([
        'achieved_at' => Carbon::parse('2026-09-03 12:00:00'),
        'accuracy' => 1000,
        'legacy' => false,
        ...$attributes,
    ]));
}

it('writes level-ups in the first person', function () {
    expect(feedCopy(FeedEvent::TYPE_LEVEL_UP, ['skill' => 'Hunter', 'level' => 25]))->toBe([
        'title' => 'Levelled up Hunter.',
        'body' => "I've reached level 25 in the Hunter skill.",
    ])->and(feedCopy(FeedEvent::TYPE_LEVEL_UP, ['skill' => 'cooking', 'level' => 99]))->toBe([
        'title' => 'Mastered Cooking.',
        'body' => "I've reached level 99 in the Cooking skill, the highest level possible.",
    ])->and(feedCopy(FeedEvent::TYPE_LEVEL_UP, ['skill' => 'Runecraft', 'level' => 50])['title'])
        ->toBe('Levelled up Runecraft.');
});

it('writes quests, combat tasks, collection log slots and pets', function () {
    expect(feedCopy(FeedEvent::TYPE_QUEST_COMPLETE, ['quest' => 'Dragon Slayer I']))->toBe([
        'title' => 'Quest complete: Dragon Slayer I.',
        'body' => 'I completed the quest Dragon Slayer I.',
    ])->and(feedCopy(FeedEvent::TYPE_COMBAT_ACHIEVEMENT, ['task' => 'Peach Conjurer', 'tier' => 'elite']))->toBe([
        'title' => 'Combat task complete.',
        'body' => "I completed the Elite combat task 'Peach Conjurer'.",
    ])->and(feedCopy(FeedEvent::TYPE_COMBAT_ACHIEVEMENT, ['task' => 'Whack-a-Mole', 'tier' => null])['body'])
        ->toBe("I completed the combat task 'Whack-a-Mole'.")
        ->and(feedCopy(FeedEvent::TYPE_COLLECTION_LOG, ['item' => 'Twisted bow']))->toBe([
            'title' => 'New collection log item.',
            'body' => 'I added Twisted bow to my collection log.',
        ])->and(feedCopy(FeedEvent::TYPE_PET, []))->toBe([
            'title' => 'Found a pet!',
            'body' => "I have a funny feeling I'm being followed.",
        ]);
});

it('names the top item of a loot drop', function () {
    $payload = ['source' => 'Zulrah', 'items' => [['id' => 12922, 'quantity' => 1], ['id' => 12934, 'quantity' => 300]], 'total_value' => 3_450_000];

    expect(feedCopy(FeedEvent::TYPE_LOOT_DROP, $payload, 'Tanzanite fang'))->toBe([
        'title' => 'Found Tanzanite fang.',
        'body' => 'I received Tanzanite fang (+1 more) worth 3,450,000 coins from Zulrah.',
    ])->and(feedCopy(FeedEvent::TYPE_LOOT_DROP, [...$payload, 'items' => [$payload['items'][0]]], 'Tanzanite fang')['body'])
        ->toBe('I received Tanzanite fang worth 3,450,000 coins from Zulrah.')
        ->and(feedCopy(FeedEvent::TYPE_LOOT_DROP, $payload)['title'])
        ->toBe('Received loot from Zulrah.');
});

it('writes achievements by measure', function () {
    expect(achievementCopy(['name' => '99 Sailing', 'metric' => 'sailing', 'measure' => 'experience', 'threshold' => 13_034_431]))
        ->toBe(['title' => '99 Sailing', 'body' => "I've reached level 99 in the Sailing skill."])
        ->and(achievementCopy(['name' => '50m Strength', 'metric' => 'strength', 'measure' => 'experience', 'threshold' => 50_000_000])['body'])
        ->toBe("I've reached 50m experience in Strength.")
        ->and(achievementCopy(['name' => '500m Overall Exp.', 'metric' => 'overall', 'measure' => 'experience', 'threshold' => 500_000_000])['body'])
        ->toBe("I've reached 500m total experience.")
        ->and(achievementCopy(['name' => '1k Yama kills', 'metric' => 'yama', 'measure' => 'kills', 'threshold' => 1000])['body'])
        ->toBe("I've killed Yama 1,000 times.")
        ->and(achievementCopy(['name' => '500 Clue Scrolls (Hard)', 'metric' => 'clue_scrolls_hard', 'measure' => 'score', 'threshold' => 500])['body'])
        ->toBe("I've reached 500 in Clue Scrolls (Hard).")
        ->and(achievementCopy(['name' => 'Base 90 Stats', 'metric' => 'overall', 'measure' => 'levels', 'threshold' => 128_311_968])['body'])
        ->toBe("I've reached Base 90 Stats.")
        ->and(achievementCopy(['name' => 'Maxed Overall', 'metric' => 'overall', 'measure' => 'levels', 'threshold' => 312_826_344])['body'])
        ->toBe("I've reached level 99 in every skill.");
});

it('says "around" when WOM is unsure of the date by more than a day', function () {
    $copy = achievementCopy([
        'name' => '1k Yama kills', 'metric' => 'yama', 'measure' => 'kills', 'threshold' => 1000,
        'accuracy' => 2 * 86_400_000,
    ]);

    expect($copy['body'])->toBe("Around 3 September 2026, I've killed Yama 1,000 times.");
});

it('writes personal records with the shortest period\'s value and names the other bests', function () {
    expect(AdventurersLogCopy::forRecord('strength', ['week' => 8_002_523]))->toBe([
        'title' => 'New personal best: Strength.',
        'body' => 'I gained 8,002,523 Strength experience in a single week, my best yet.',
    ])->and(AdventurersLogCopy::forRecord('vorkath', ['year' => 202, 'month' => 150])['body'])
        ->toBe('I killed Vorkath 150 times in a single month, my best yet (also a new yearly best).')
        ->and(AdventurersLogCopy::forRecord('clue_scrolls_all', ['year' => 451, 'week' => 74, 'month' => 160])['body'])
        ->toBe('I scored 74 in Clue Scrolls (All) in a single week, my best yet (also a new monthly and yearly best).')
        ->and(AdventurersLogCopy::forRecord('overall', ['week' => 21_656_266.0])['body'])
        ->toBe('I gained 21,656,266 experience in a single week, my best yet.');
});

it('falls back to generic wording for a malformed payload', function (string $type, array $payload, array $expected) {
    expect(feedCopy($type, $payload))->toBe($expected);
})->with([
    'quest without a name' => [FeedEvent::TYPE_QUEST_COMPLETE, [], ['title' => 'Quest complete.', 'body' => 'I completed a quest.']],
    'combat task without a task' => [FeedEvent::TYPE_COMBAT_ACHIEVEMENT, ['tier' => 'elite'], ['title' => 'Combat task complete.', 'body' => 'I completed an Elite combat task.']],
    'combat task with nothing' => [FeedEvent::TYPE_COMBAT_ACHIEVEMENT, ['task' => ['nested']], ['title' => 'Combat task complete.', 'body' => 'I completed a combat task.']],
    'collection log without an item' => [FeedEvent::TYPE_COLLECTION_LOG, ['item' => ''], ['title' => 'New collection log item.', 'body' => 'I added an item to my collection log.']],
    'level-up without a skill' => [FeedEvent::TYPE_LEVEL_UP, ['level' => 50], ['title' => 'Levelled up.', 'body' => "I've reached level 50 in a skill."]],
    'loot without a source or items' => [FeedEvent::TYPE_LOOT_DROP, ['items' => 'broken'], ['title' => 'Received loot from a monster.', 'body' => 'I received loot worth 0 coins from a monster.']],
]);
