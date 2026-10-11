<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomProgress;

/**
 * @param  array<string, int>  $skills
 * @param  array<string, int>  $bosses
 */
function womProgressDay(int $accountId, int $daysAgo, int $exp, array $skills = [], array $bosses = []): void
{
    WomDailySnapshot::query()->insert([
        'account_id' => $accountId,
        'date' => now('UTC')->subDays($daysAgo)->toDateString(),
        'exp' => $exp,
        'ehp' => 0.0,
        'ehb' => 0.0,
        'skills' => $skills,
        'bosses' => $bosses,
        'activities' => [],
    ]);
}

it('sums the week\'s exp, levels and boss kills over active, visible, linked members', function () {
    $counted = WomPlayer::factory()->create();
    $inactive = WomPlayer::factory()->inactive()->create();
    $hidden = WomPlayer::factory()->create(['show_on_progress' => false]);
    $unlinked = WomPlayer::factory()->notFound()->create();

    womProgressDay($counted->account_id, 8, 1_000, ['attack' => 0], ['zulrah' => 10]);
    womProgressDay($counted->account_id, 0, 3_000, ['attack' => 1_154], ['zulrah' => 25]);
    foreach ([$inactive, $hidden, $unlinked] as $excluded) {
        womProgressDay($excluded->account_id, 8, 0, ['attack' => 0], ['zulrah' => 0]);
        womProgressDay($excluded->account_id, 0, 99_999_999, ['attack' => 99_999_999], ['zulrah' => 999]);
    }

    expect(app(WomProgress::class)->stats())->toBe([
        'active_members' => 1,
        'week_exp_gained' => 2_000,
        'week_levels_gained' => 9,
        'week_boss_kills' => 15,
    ]);
});

it('is empty when nobody is tracked', function () {
    Account::factory()->create();

    expect(app(WomProgress::class)->stats())->toBe([
        'active_members' => 0,
        'week_exp_gained' => 0,
        'week_levels_gained' => 0,
        'week_boss_kills' => 0,
    ]);
});
