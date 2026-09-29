<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomLeaderboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * A board member with a snapshot 8 days ago and one today.
 *
 * @param  array{0: int, 1: int}  $exp
 * @param  array{0: array<string, int>, 1: array<string, int>}  $skills
 * @param  array{0: array<string, int>, 1: array<string, int>}  $bosses
 */
function womBoardPlayer(string $username, array $exp, array $skills = [[], []], array $bosses = [[], []], array $attributes = []): WomPlayer
{
    $player = WomPlayer::factory()
        ->for(Account::factory()->state(['username' => $username]))
        ->create($attributes);

    foreach ([8, 0] as $index => $daysAgo) {
        WomDailySnapshot::query()->insert([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp[$index],
            'ehp' => 0.0,
            'ehb' => 0.0,
            'skills' => $skills[$index],
            'bosses' => $bosses[$index],
            'activities' => [],
        ]);
    }

    return $player;
}

it('ranks the period top gainers of overall and skips the excluded population', function () {
    womBoardPlayer('Second', [1_000, 3_000]);
    womBoardPlayer('First', [1_000, 9_000]);
    womBoardPlayer('Idle', [5_000, 5_000]);
    womBoardPlayer('Hidden', [0, 1_000_000], attributes: ['show_on_progress' => false]);
    womBoardPlayer('Dormant', [0, 1_000_000], attributes: ['last_changed_at' => now()->subYear()]);

    $board = app(WomLeaderboard::class)->topGainers('week', 'overall');

    expect(array_column($board, 'username'))->toBe(['First', 'Second'])
        ->and($board[0])->toMatchArray(['gained' => 8_000])
        ->and($board[0])->toHaveKeys(['account_id', 'username', 'account_type', 'gained']);
});

it('ranks gainers of a boss, leaves out new kill counts and honours the limit', function () {
    womBoardPlayer('A', [0, 0], bosses: [['zulrah' => 10], ['zulrah' => 30]]);
    womBoardPlayer('B', [0, 0], bosses: [['zulrah' => 10], ['zulrah' => 60]]);
    womBoardPlayer('C', [0, 0], bosses: [['zulrah' => 10], ['zulrah' => 15]]);
    womBoardPlayer('New', [0, 0], bosses: [[], ['zulrah' => 900]]);

    $board = app(WomLeaderboard::class)->topGainers('week', 'zulrah', 2);

    expect(array_column($board, 'username'))->toBe(['B', 'A'])
        ->and($board[0]['gained'])->toBe(50);
});

it('reads an unknown metric as overall', function () {
    womBoardPlayer('Only', [0, 700]);

    expect(app(WomLeaderboard::class)->topGainers('week', 'ehp')[0]['gained'])->toBe(700);
});

it('names the top gainer of every trained skill, in skill order', function () {
    womBoardPlayer('Cook', [0, 0], skills: [['cooking' => 0, 'attack' => 0], ['cooking' => 5_000, 'attack' => 100]]);
    womBoardPlayer('Fighter', [0, 0], skills: [['attack' => 0], ['attack' => 900]]);
    womBoardPlayer('Hidden', [0, 0], skills: [['attack' => 0], ['attack' => 99_999]], attributes: ['show_on_progress' => false]);

    $leaders = app(WomLeaderboard::class)->skillLeaders('week');

    expect(array_column($leaders, 'metric'))->toBe(['attack', 'cooking'])
        ->and($leaders[0])->toMatchArray([
            'label' => 'Attack',
            'icon' => ['kind' => 'skill', 'slug' => 'attack'],
            'username' => 'Fighter',
            'gained' => 900,
        ])
        ->and($leaders[1])->toMatchArray(['username' => 'Cook', 'gained' => 5_000])
        ->and($leaders[1])->toHaveKeys(['account_id', 'account_type']);
});

it('caches the top gainers per period, metric and limit', function () {
    womBoardPlayer('Cached', [0, 1_000]);

    $first = app(WomLeaderboard::class)->topGainers('week', 'overall', 5);
    womBoardPlayer('Later', [0, 9_000]);

    expect(app(WomLeaderboard::class)->topGainers('week', 'overall', 5))->toBe($first)
        ->and(array_column(app(WomLeaderboard::class)->topGainers('week', 'overall', 6), 'username'))->toBe(['Later', 'Cached']);
});
