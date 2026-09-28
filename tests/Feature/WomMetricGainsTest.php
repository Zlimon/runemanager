<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WomMetricGains;
use App\Services\WiseOldMan\WomSnapshotHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * One daily snapshot with its metric maps; pass null for a map to store a
 * headline-only doc (written before the maps existed).
 *
 * @param  array<string, int>|null  $skills
 * @param  array<string, int>|null  $bosses
 * @param  array<string, int>|null  $activities
 */
function womGainsDay(int $accountId, int $daysAgo, int $exp, ?array $skills = [], ?array $bosses = [], ?array $activities = []): void
{
    WomDailySnapshot::query()->insert(array_filter([
        'account_id' => $accountId,
        'date' => now('UTC')->subDays($daysAgo)->toDateString(),
        'exp' => $exp,
        'ehp' => 0.0,
        'ehb' => 0.0,
        'skills' => $skills,
        'bosses' => $bosses,
        'activities' => $activities,
    ], fn ($value): bool => $value !== null));
}

function womGainsAccount(): Account
{
    return WomPlayer::factory()->create()->account;
}

/**
 * @param  list<array<string, mixed>>  $rows
 * @return array<string, array<string, mixed>>
 */
function womGainsByMetric(array $rows): array
{
    return array_column($rows, null, 'metric');
}

it('gains a metric both snapshots carry, flags one only the latest has and drops one it lacks', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 10, 1_000_000, ['attack' => 1_000_000, 'cooking' => 50], ['zulrah' => 100, 'vorkath' => 40]);
    womGainsDay($account->id, 0, 1_600_000, ['attack' => 1_500_000, 'slayer' => 100_000], ['zulrah' => 130], ['clue_scrolls_all' => 12]);

    $gains = app(WomMetricGains::class)->forAccount($account, 'week');
    $skills = womGainsByMetric($gains['skills']);
    $bosses = womGainsByMetric($gains['bosses']);

    expect($gains['period'])->toBe('week')
        ->and($gains['since'])->toBeNull()
        ->and(array_column($gains['skills'], 'metric'))->toBe(['overall', 'attack', 'slayer'])
        ->and($skills['attack'])->toMatchArray(['label' => 'Attack', 'xp' => 1_500_000, 'level' => 77, 'gained' => 500_000, 'levels_gained' => 4])
        ->and($skills['attack']['icon'])->toBe(['kind' => 'skill', 'slug' => 'attack'])
        ->and($skills['slayer'])->toMatchArray(['gained' => null, 'levels_gained' => null])
        ->and($bosses)->toHaveKeys(['zulrah'])->not->toHaveKey('vorkath')
        ->and($bosses['zulrah'])->toMatchArray(['kills' => 130, 'gained' => 30])
        ->and($gains['activities'][0])->toMatchArray(['metric' => 'clue_scrolls_all', 'score' => 12, 'gained' => null]);
});

it('reads overall from the headline exp and sums skill levels into it', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 8, 2_000_000, ['attack' => 1_000_000]);
    womGainsDay($account->id, 0, 3_000_000, ['attack' => 1_500_000]);

    $overall = app(WomMetricGains::class)->forAccount($account, 'week')['skills'][0];

    // The 23 other skills count as level 1 each; attack is 77.
    expect($overall)->toMatchArray([
        'metric' => 'overall',
        'xp' => 3_000_000,
        'level' => 23 + 77,
        'gained' => 1_000_000,
        'levels_gained' => 4,
    ]);
});

it('measures the maps from the oldest snapshot that has them, and overall from any', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 400, 1_000_000, null, null, null);
    womGainsDay($account->id, 300, 1_200_000, ['attack' => 1_000_000], ['zulrah' => 10]);
    womGainsDay($account->id, 0, 2_500_000, ['attack' => 1_500_000], ['zulrah' => 25]);

    $gains = app(WomMetricGains::class)->forAccount($account, 'year');
    $skills = womGainsByMetric($gains['skills']);

    expect($gains['since'])->toBe(now('UTC')->subDays(300)->toDateString())
        ->and($skills['overall'])->toMatchArray(['gained' => 1_500_000, 'levels_gained' => 4])
        ->and($skills['attack']['gained'])->toBe(500_000)
        ->and($gains['bosses'][0]['gained'])->toBe(15)
        ->and(app(WomMetricGains::class)->forPopulation([$account->id], 'year', 'zulrah'))->toBe([$account->id => 15])
        ->and(app(WomMetricGains::class)->forPopulation([$account->id], 'year', 'overall'))->toBe([$account->id => 1_500_000])
        ->and(app(WomMetricGains::class)->forPopulationGroup([$account->id], 'year', 'skill'))->toBe([$account->id => ['attack' => 500_000]]);
});

it('measures past a headline-only snapshot at the period start, since the first mapped date', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 30, 1_000_000, null, null, null);
    womGainsDay($account->id, 20, 1_100_000, ['cooking' => 200_000], ['zulrah' => 40], ['clue_scrolls_all' => 3]);
    womGainsDay($account->id, 0, 1_400_000, ['cooking' => 450_000], ['zulrah' => 70], ['clue_scrolls_all' => 5]);

    $gains = app(WomMetricGains::class)->forAccount($account, 'month');
    $skills = womGainsByMetric($gains['skills']);

    expect($gains['since'])->toBe(now('UTC')->subDays(20)->toDateString())
        ->and($skills['overall']['gained'])->toBe(400_000)
        ->and($skills['cooking']['gained'])->toBe(250_000)
        ->and($gains['bosses'][0]['gained'])->toBe(30)
        ->and($gains['activities'][0]['gained'])->toBe(2);
});

it('measures a metric first ranked inside the period from its first appearance, and keeps one first seen today new', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 40, 0, ['attack' => 100], []);
    womGainsDay($account->id, 10, 0, ['attack' => 200], ['zulrah' => 5]);
    womGainsDay($account->id, 0, 0, ['attack' => 300], ['zulrah' => 25, 'vorkath' => 50]);

    $gains = app(WomMetricGains::class)->forAccount($account, 'month');
    $bosses = womGainsByMetric($gains['bosses']);

    expect($gains['since'])->toBeNull()
        ->and($bosses['zulrah']['gained'])->toBe(20)
        ->and($bosses['vorkath']['gained'])->toBeNull()
        ->and(womGainsByMetric($gains['skills'])['attack']['gained'])->toBe(200);
});

it('ranks a population mixing mapped, late-mapped, newly ranked and headline-only histories', function () {
    $mapped = womGainsAccount();
    $lateMapped = womGainsAccount();
    $newlyRanked = womGainsAccount();
    $headlineOnly = womGainsAccount();

    womGainsDay($mapped->id, 400, 0, [], ['zulrah' => 100]);
    womGainsDay($mapped->id, 0, 0, [], ['zulrah' => 160]);
    womGainsDay($lateMapped->id, 400, 0, null, null, null);
    womGainsDay($lateMapped->id, 200, 0, [], ['zulrah' => 10]);
    womGainsDay($lateMapped->id, 0, 0, [], ['zulrah' => 110]);
    womGainsDay($newlyRanked->id, 400, 0, [], []);
    womGainsDay($newlyRanked->id, 0, 0, [], ['zulrah' => 999]);
    womGainsDay($headlineOnly->id, 400, 0, null, null, null);
    womGainsDay($headlineOnly->id, 0, 0, null, null, null);

    $ids = [$mapped->id, $lateMapped->id, $newlyRanked->id, $headlineOnly->id];
    $gains = app(WomMetricGains::class);

    expect($gains->forPopulation($ids, 'year', 'zulrah'))->toBe([$lateMapped->id => 100, $mapped->id => 60])
        ->and($gains->forPopulationGroup($ids, 'year', 'boss'))->toEqual([$mapped->id => ['zulrah' => 60], $lateMapped->id => ['zulrah' => 100]])
        ->and($gains->totals($ids, 'year')['boss_kills'])->toBe(160);
});

it('ranks a population with the same gains the account path computes', function () {
    $accounts = collect(range(1, 5))->map(fn (): Account => womGainsAccount());
    [$mapped, $lateMapped, $newlyRanked, $headlineOnly, $midPeriod] = $accounts->all();

    womGainsDay($mapped->id, 400, 0, ['attack' => 1_000], ['zulrah' => 100, 'vorkath' => 3]);
    womGainsDay($mapped->id, 100, 0, ['attack' => 5_000], ['zulrah' => 120, 'vorkath' => 3]);
    womGainsDay($mapped->id, 0, 0, ['attack' => 9_000], ['zulrah' => 160, 'vorkath' => 9]);
    womGainsDay($lateMapped->id, 400, 0, null, null, null);
    womGainsDay($lateMapped->id, 200, 0, ['attack' => 50], ['zulrah' => 10]);
    womGainsDay($lateMapped->id, 0, 0, ['attack' => 70_000], ['zulrah' => 110]);
    womGainsDay($newlyRanked->id, 400, 0, [], []);
    womGainsDay($newlyRanked->id, 0, 0, ['attack' => 2_000], ['zulrah' => 999]);
    womGainsDay($headlineOnly->id, 400, 0, null, null, null);
    womGainsDay($headlineOnly->id, 0, 0, null, null, null);
    womGainsDay($midPeriod->id, 400, 0, ['attack' => 10], []);
    womGainsDay($midPeriod->id, 150, 0, ['attack' => 20], ['zulrah' => 5]);
    womGainsDay($midPeriod->id, 50, 0, ['attack' => 30], ['zulrah' => 8, 'nex' => 2]);
    womGainsDay($midPeriod->id, 0, 0, ['attack' => 40], ['zulrah' => 12, 'nex' => 7]);

    $gains = app(WomMetricGains::class);
    $ids = $accounts->pluck('id')->all();

    foreach (['skill' => 'skills', 'boss' => 'bosses'] as $group => $key) {
        $perAccount = [];
        foreach ($accounts as $account) {
            foreach ($gains->forAccount($account, 'year')[$key] as $row) {
                if ($row['metric'] !== 'overall' && $row['gained'] > 0) {
                    $perAccount[$account->id][$row['metric']] = $row['gained'];
                }
            }
        }

        expect($gains->forPopulationGroup($ids, 'year', $group))->toEqual($perAccount);
    }

    expect($gains->forPopulationGroup($ids, 'year', 'boss'))->toEqual([
        $mapped->id => ['zulrah' => 60, 'vorkath' => 6],
        $lateMapped->id => ['zulrah' => 100],
        $midPeriod->id => ['zulrah' => 7, 'nex' => 5],
    ])
        ->and($gains->forPopulation($ids, 'year', 'nex'))->toBe([$midPeriod->id => 5])
        ->and($gains->totals($ids, 'year')['boss_kills'])->toBe(60 + 6 + 100 + 7 + 5);
});

it('measures from the first snapshot and says since when when history starts inside the period', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 3, 1_000, ['attack' => 1_000]);
    womGainsDay($account->id, 0, 4_000, ['attack' => 4_000]);

    $gains = app(WomMetricGains::class)->forAccount($account, 'month');

    expect($gains['since'])->toBe(now('UTC')->subDays(3)->toDateString())
        ->and($gains['skills'][0]['gained'])->toBe(3_000);
});

it('uses the last snapshot on or before the period start as the baseline', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 40, 100, ['attack' => 100]);
    womGainsDay($account->id, 9, 1_000, ['attack' => 1_000]);
    womGainsDay($account->id, 5, 1_500, ['attack' => 1_500]);
    womGainsDay($account->id, 0, 2_000, ['attack' => 2_000]);

    expect(womGainsByMetric(app(WomMetricGains::class)->forAccount($account, 'week')['skills'])['attack']['gained'])
        ->toBe(1_000);
});

it('never reports a negative gain', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 8, 1_000, [], ['zulrah' => 50]);
    womGainsDay($account->id, 0, 1_000, [], ['zulrah' => 20]);

    expect(app(WomMetricGains::class)->forAccount($account, 'week')['bosses'][0]['gained'])->toBe(0);
});

it('is empty for an unlinked account or one without snapshots, and falls back to a week', function () {
    $unlinked = WomPlayer::factory()->notFound()->create()->account;
    womGainsDay($unlinked->id, 0, 1_000, ['attack' => 1_000]);

    expect(app(WomMetricGains::class)->forAccount($unlinked, 'week'))
        ->toBe(['period' => 'week', 'since' => null, 'skills' => [], 'bosses' => [], 'activities' => []])
        ->and(app(WomMetricGains::class)->forAccount(womGainsAccount(), 'decade'))
        ->toBe(['period' => 'week', 'since' => null, 'skills' => [], 'bosses' => [], 'activities' => []]);
});

it('caches an account\'s gains until its next sync', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 8, 1_000, ['attack' => 1_000]);
    womGainsDay($account->id, 0, 2_000, ['attack' => 2_000]);

    $first = app(WomMetricGains::class)->forAccount($account, 'week');
    WomDailySnapshot::query()->where('account_id', $account->id)->where('exp', 2_000)->update(['exp' => 9_000]);

    expect(app(WomMetricGains::class)->forAccount($account->fresh(), 'week'))->toBe($first);

    $account->womPlayer->update(['synced_at' => now()->addMinute()]);

    expect(app(WomMetricGains::class)->forAccount($account->fresh(), 'week')['skills'][0]['gained'])->toBe(8_000);

    WomDailySnapshot::query()->where('account_id', $account->id)->where('exp', 9_000)->update(['exp' => 12_000]);
    $account->womPlayer->update(['backfilled_at' => now()]);

    expect(app(WomMetricGains::class)->forAccount($account->fresh(), 'week')['skills'][0]['gained'])->toBe(11_000);
});

it('ranks a population by one metric, leaving out unknown and zero gains', function () {
    $climber = womGainsAccount();
    $steady = womGainsAccount();
    $newcomer = womGainsAccount();
    $idle = womGainsAccount();

    womGainsDay($climber->id, 8, 0, [], ['zulrah' => 10]);
    womGainsDay($climber->id, 0, 0, [], ['zulrah' => 90]);
    womGainsDay($steady->id, 8, 0, [], ['zulrah' => 10]);
    womGainsDay($steady->id, 0, 0, [], ['zulrah' => 30]);
    womGainsDay($newcomer->id, 8, 0, [], []);
    womGainsDay($newcomer->id, 0, 0, [], ['zulrah' => 500]);
    womGainsDay($idle->id, 8, 0, [], ['zulrah' => 5]);
    womGainsDay($idle->id, 0, 0, [], ['zulrah' => 5]);

    $ids = [$steady->id, $climber->id, $newcomer->id, $idle->id];

    expect(app(WomMetricGains::class)->forPopulation($ids, 'week', 'zulrah'))
        ->toBe([$climber->id => 80, $steady->id => 20])
        ->and(app(WomMetricGains::class)->forPopulation([], 'week', 'zulrah'))->toBe([]);
});

it('ranks overall from the headline exp and reads an unknown metric as overall', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 8, 1_000, null, null, null);
    womGainsDay($account->id, 0, 1_750, null, null, null);

    expect(app(WomMetricGains::class)->forPopulation([$account->id], 'week', 'overall'))->toBe([$account->id => 750])
        ->and(app(WomMetricGains::class)->forPopulation([$account->id], 'week', 'nope'))->toBe([$account->id => 750]);
});

it('gains every metric of a group per account', function () {
    $a = womGainsAccount();
    $b = womGainsAccount();
    womGainsDay($a->id, 8, 0, ['attack' => 100, 'cooking' => 100]);
    womGainsDay($a->id, 0, 0, ['attack' => 400, 'cooking' => 100, 'slayer' => 50]);
    womGainsDay($b->id, 8, 0, ['attack' => 0]);
    womGainsDay($b->id, 0, 0, ['attack' => 900]);

    expect(app(WomMetricGains::class)->forPopulationGroup([$a->id, $b->id], 'week', 'skill'))
        ->toEqual([$a->id => ['attack' => 300], $b->id => ['attack' => 900]]);
});

it('totals the population\'s exp, levels and boss kills', function () {
    $a = womGainsAccount();
    $b = womGainsAccount();
    womGainsDay($a->id, 8, 1_000, ['attack' => 0], ['zulrah' => 10, 'vorkath' => 1]);
    womGainsDay($a->id, 0, 3_000, ['attack' => 1_154], ['zulrah' => 15, 'vorkath' => 3, 'nex' => 9]);
    womGainsDay($b->id, 8, 500, null, null, null);
    womGainsDay($b->id, 0, 800, ['attack' => 13_034_431], ['zulrah' => 100]);

    // a: attack 1 → 10 (9 levels), 5 + 2 kills (nex is new); b: only its exp is comparable.
    expect(app(WomMetricGains::class)->totals([$a->id, $b->id], 'week'))
        ->toBe(['exp' => 2_300, 'levels' => 9, 'boss_kills' => 7]);
});

it('charts one metric\'s daily values over a range, skipping days without it', function () {
    $account = womGainsAccount();
    womGainsDay($account->id, 40, 100, [], ['zulrah' => 1]);
    womGainsDay($account->id, 5, 200, [], []);
    womGainsDay($account->id, 3, 300, [], ['zulrah' => 7]);
    womGainsDay($account->id, 1, 400, [], ['zulrah' => 9]);

    $gains = app(WomMetricGains::class);

    expect($gains->series($account, 'zulrah', 'month'))->toBe([
        ['t' => now('UTC')->subDays(3)->startOfDay()->getTimestampMs(), 'v' => 7],
        ['t' => now('UTC')->subDays(1)->startOfDay()->getTimestampMs(), 'v' => 9],
    ])
        ->and(array_column($gains->series($account, 'overall', 'all'), 'v'))->toBe([100, 200, 300, 400])
        ->and(array_column($gains->series($account, 'nope', 'week'), 'v'))->toBe([200, 300, 400])
        ->and($gains->series(WomPlayer::factory()->notFound()->create()->account, 'overall', 'all'))->toBe([]);
});

it('thins long series to at most the point budget, keeping both ends', function () {
    $thinned = app(WomSnapshotHistory::class)->downsample(range(1, 1000));

    expect(count($thinned))->toBeLessThanOrEqual(WomSnapshotHistory::MAX_POINTS)
        ->and($thinned[0])->toBe(1)
        ->and(end($thinned))->toBe(1000);
});
