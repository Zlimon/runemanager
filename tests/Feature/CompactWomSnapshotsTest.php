<?php

use App\Models\WomDailySnapshot;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Sunday; the cutoff 400 days back is Saturday 2025-08-23 (ISO week 34).
    $this->travelTo(Carbon::parse('2026-09-27 05:30:00', 'UTC'));
});

function insertWomDays(int $accountId, array $dates): void
{
    WomDailySnapshot::query()->insert(array_map(
        fn (string $date): array => ['account_id' => $accountId, 'date' => $date, 'exp' => 1, 'ehp' => 0.0, 'ehb' => 0.0],
        $dates,
    ));
}

function womDates(int $accountId): array
{
    return WomDailySnapshot::where('account_id', $accountId)->orderBy('date')->pluck('date')->all();
}

it('keeps the last document of each ISO week once it is older than 400 days', function () {
    insertWomDays(1, collect(CarbonPeriod::create('2025-08-04', '2025-08-30'))->map->toDateString()->all());
    insertWomDays(2, ['2024-12-30', '2024-12-31', '2025-01-01', '2025-08-05', '2025-08-06']);

    $this->artisan('wom:compact-snapshots')->expectsOutput('Removed 20 snapshots.')->assertSuccessful();

    expect(womDates(1))->toBe([
        '2025-08-10', // week 32
        '2025-08-17', // week 33
        // week 34 straddles the cutoff: its old days go, the week keeps 08-23/08-24
        '2025-08-23', '2025-08-24',
        '2025-08-25', '2025-08-26', '2025-08-27', '2025-08-28', '2025-08-29', '2025-08-30',
    ])
        // 2024-12-30 … 2025-01-01 are all ISO week 2025-01.
        ->and(womDates(2))->toBe(['2025-01-01', '2025-08-06']);
});

it('leaves everything newer than 400 days alone', function () {
    insertWomDays(1, collect(CarbonPeriod::create('2025-08-23', '2026-09-27'))->map->toDateString()->all());

    $this->artisan('wom:compact-snapshots')->expectsOutput('Removed 0 snapshots.')->assertSuccessful();

    expect(WomDailySnapshot::where('account_id', 1)->count())->toBe(401);
});
