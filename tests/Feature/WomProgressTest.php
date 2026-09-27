<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function womProgressDay(int $accountId, int $daysAgo, int $exp, float $ehp = 0, float $ehb = 0): void
{
    WomDailySnapshot::query()->create([
        'account_id' => $accountId,
        'date' => now('UTC')->subDays($daysAgo)->toDateString(),
        'exp' => $exp,
        'ehp' => $ehp,
        'ehb' => $ehb,
    ]);
}

it('counts only active, visible, linked members in the stats', function () {
    $counted = WomPlayer::factory()->create(['ehp' => 100, 'ehb' => 10]);
    $inactive = WomPlayer::factory()->inactive()->create(['ehp' => 500, 'ehb' => 50]);
    $hidden = WomPlayer::factory()->create(['ehp' => 700, 'ehb' => 70, 'show_on_progress' => false]);
    $unlinked = WomPlayer::factory()->notFound()->create();

    womProgressDay($counted->account_id, 8, 1_000);
    womProgressDay($counted->account_id, 0, 3_000);
    foreach ([$inactive, $hidden, $unlinked] as $excluded) {
        womProgressDay($excluded->account_id, 8, 0);
        womProgressDay($excluded->account_id, 0, 99_999);
    }

    expect(app(WomProgress::class)->stats())->toBe([
        'active_members' => 1,
        'total_ehp' => 100.0,
        'total_ehb' => 10.0,
        'week_exp_gained' => 2_000,
    ]);
});

it('charts the members\' summed gains since the range start, forward-filling missed days', function () {
    $a = WomPlayer::factory()->create();
    $b = WomPlayer::factory()->create();

    // Baselines at or before the range start: 100 for a, 500 for b.
    womProgressDay($a->account_id, 10, 100, 1.0);
    womProgressDay($b->account_id, 20, 500, 5.0);
    womProgressDay($a->account_id, 3, 150, 1.5);
    womProgressDay($b->account_id, 2, 600, 6.0);
    womProgressDay($a->account_id, 1, 200, 2.0);

    $series = app(WomProgress::class)->instanceSeries('week');

    // Day -3: a +50. Day -2: a +50 (carried), b +100. Day -1: a +100, b +100.
    expect(array_column($series, 'exp'))->toBe([50, 150, 200])
        ->and(array_column($series, 'ehp'))->toBe([0.5, 1.5, 2.0]);
});

it('does not add a step of a newly linked member\'s whole XP', function () {
    $veteran = WomPlayer::factory()->create();
    $newcomer = WomPlayer::factory()->create();

    womProgressDay($veteran->account_id, 10, 1_000);
    womProgressDay($veteran->account_id, 2, 1_100);
    // History starts inside the range: measured from its first point.
    womProgressDay($newcomer->account_id, 2, 50_000_000);
    womProgressDay($newcomer->account_id, 1, 50_000_300);

    expect(array_column(app(WomProgress::class)->instanceSeries('week'), 'exp'))->toBe([100, 400]);
});

it('measures the all-time range from each member\'s first point', function () {
    $player = WomPlayer::factory()->create();
    womProgressDay($player->account_id, 400, 1_000);
    womProgressDay($player->account_id, 1, 4_000);

    expect(array_column(app(WomProgress::class)->instanceSeries('all'), 'exp'))->toBe([0, 3_000]);
});

it('leaves hidden and inactive members out of the series', function () {
    $counted = WomPlayer::factory()->create();
    $hidden = WomPlayer::factory()->create(['show_on_progress' => false]);

    womProgressDay($counted->account_id, 1, 100);
    womProgressDay($hidden->account_id, 1, 1_000_000);

    expect(array_column(app(WomProgress::class)->instanceSeries('month'), 'exp'))->toBe([0]);
});

it('is empty when nobody is tracked', function () {
    Account::factory()->create();

    expect(app(WomProgress::class)->instanceSeries('all'))->toBe([])
        ->and(app(WomProgress::class)->stats()['active_members'])->toBe(0);
});
