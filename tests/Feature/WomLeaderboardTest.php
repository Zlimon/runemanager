<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomLeaderboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function womBoardPlayer(string $username, int $weekAgoExp, int $nowExp, array $attributes = []): WomPlayer
{
    $player = WomPlayer::factory()
        ->for(Account::factory()->state(['username' => $username]))
        ->create($attributes);

    foreach ([[8, $weekAgoExp], [0, $nowExp]] as [$daysAgo, $exp]) {
        WomDailySnapshot::query()->create([
            'account_id' => $player->account_id,
            'date' => now('UTC')->subDays($daysAgo)->toDateString(),
            'exp' => $exp,
            'ehp' => $exp / 1000,
            'ehb' => 0,
        ]);
    }

    return $player;
}

it('ranks the period top gainers and skips the excluded population', function () {
    womBoardPlayer('Second', 1_000, 3_000);
    womBoardPlayer('First', 1_000, 9_000);
    womBoardPlayer('Idle', 5_000, 5_000);
    womBoardPlayer('Hidden', 0, 1_000_000, ['show_on_progress' => false]);
    womBoardPlayer('Dormant', 0, 1_000_000, ['last_changed_at' => now()->subYear()]);

    $board = app(WomLeaderboard::class)->topGainers('week', 'exp');

    expect(array_column($board, 'username'))->toBe(['First', 'Second'])
        ->and($board[0])->toMatchArray(['gained' => 8_000])
        ->and($board[0])->toHaveKeys(['account_id', 'username', 'account_type', 'gained']);
});

it('ranks gainers by EHP and honours the limit', function () {
    womBoardPlayer('A', 0, 2_000);
    womBoardPlayer('B', 0, 5_000);
    womBoardPlayer('C', 0, 1_000);

    $board = app(WomLeaderboard::class)->topGainers('week', 'ehp', 2);

    expect(array_column($board, 'username'))->toBe(['B', 'A'])
        ->and($board[0]['gained'])->toBe(5.0);
});

it('ranks members by current EHB over the same population', function () {
    WomPlayer::factory()->for(Account::factory()->state(['username' => 'Low']))->create(['ehb' => 10]);
    WomPlayer::factory()->for(Account::factory()->state(['username' => 'High']))->create(['ehb' => 900]);
    WomPlayer::factory()->for(Account::factory()->state(['username' => 'Hidden']))->create(['ehb' => 5000, 'show_on_progress' => false]);
    WomPlayer::factory()->inactive()->for(Account::factory()->state(['username' => 'Old']))->create(['ehb' => 5000]);

    $board = app(WomLeaderboard::class)->efficiency('ehb');

    expect(array_column($board, 'username'))->toBe(['High', 'Low'])
        ->and($board[0]['value'])->toBe(900.0);
});

it('caches the top gainers per period, metric and limit', function () {
    womBoardPlayer('Cached', 0, 1_000);

    $first = app(WomLeaderboard::class)->topGainers('week', 'exp', 5);
    womBoardPlayer('Later', 0, 9_000);

    expect(app(WomLeaderboard::class)->topGainers('week', 'exp', 5))->toBe($first)
        ->and(array_column(app(WomLeaderboard::class)->topGainers('week', 'exp', 6), 'username'))->toBe(['Later', 'Cached']);
});
