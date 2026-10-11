<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\BackfillWomSnapshots;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
    $this->travelTo(Carbon::parse('2026-09-26 12:00:00', 'UTC'));

    $this->account = Account::factory()->create(['username' => 'Bartheez']);
    $this->player = WomPlayer::factory()->for($this->account)->create(['backfilled_at' => null]);
});

/** A slim WOM snapshot: a total, one skill, one boss, one activity. */
function womSnapshot(Carbon $at, int $exp, int $zulrah = 10): array
{
    return [
        'createdAt' => $at->toIso8601ZuluString('millisecond'),
        'data' => [
            'skills' => [
                'overall' => ['metric' => 'overall', 'experience' => $exp, 'rank' => 1, 'level' => 2277],
                'attack' => ['metric' => 'attack', 'experience' => intdiv($exp, 24), 'rank' => 1, 'level' => 99],
            ],
            'bosses' => ['zulrah' => ['metric' => 'zulrah', 'kills' => $zulrah, 'rank' => 1]],
            'activities' => ['clue_scrolls_all' => ['metric' => 'clue_scrolls_all', 'score' => 5, 'rank' => 1]],
            'computed' => ['ehp' => ['value' => 100.5], 'ehb' => ['value' => 20.25]],
        ],
    ];
}

/**
 * `$count` snapshots, newest first, `$hoursApart` hours apart, starting
 * `$skipHours` hours before now.
 *
 * @return list<array<string, mixed>>
 */
function womSnapshotPage(int $count, int $hoursApart = 1, int $skipHours = 0): array
{
    return array_map(
        fn (int $i): array => womSnapshot(now()->utc()->subHours($skipHours + $i * $hoursApart), 1_000_000_000 - ($skipHours + $i * $hoursApart)),
        range(0, $count - 1),
    );
}

function snapshotRequests(): Collection
{
    return Http::recorded(fn (Request $request) => str_contains($request->url(), '/snapshots'))->map(fn (array $pair) => $pair[0]);
}

it('pages through snapshots until a short page', function () {
    Http::fakeSequence('api.wiseoldman.net/v2/players/bartheez/snapshots*')
        ->push(womSnapshotPage(200))
        ->push(womSnapshotPage(200, skipHours: 200))
        ->push(womSnapshotPage(57, skipHours: 400));

    $written = app(BackfillWomSnapshots::class)->run($this->player);

    expect(snapshotRequests()->map(fn (Request $request) => (int) $request['offset'])->all())->toBe([0, 200, 400])
        ->and(snapshotRequests()->every(fn (Request $request) => $request['period'] === 'year' && (int) $request['limit'] === 200))->toBeTrue()
        // 457 hourly snapshots back from 2026-09-26 12:00 span 20 UTC days.
        ->and($written)->toBe(20)
        ->and(WomDailySnapshot::where('account_id', $this->account->id)->count())->toBe(20)
        ->and($this->player->fresh()->backfilled_at->equalTo(now()))->toBeTrue();
});

it('stops at snapshots older than a year', function () {
    $page = [
        womSnapshot(now()->subDays(10), 900),
        womSnapshot(now()->subDays(364), 800),
        womSnapshot(now()->subDays(366), 700),
        ...womSnapshotPage(197, skipHours: 24 * 400),
    ];
    Http::fakeSequence('api.wiseoldman.net/v2/players/bartheez/snapshots*')->push($page)->push(womSnapshotPage(200));

    $written = app(BackfillWomSnapshots::class)->run($this->player);

    expect(snapshotRequests())->toHaveCount(1)
        ->and($written)->toBe(2)
        ->and(WomDailySnapshot::where('account_id', $this->account->id)->orderBy('date')->pluck('date')->all())
        ->toBe(['2025-09-27', '2026-09-16']);
});

it('makes at most MAX_PAGES calls for a heavily tracked player', function () {
    Http::fake(['api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response(womSnapshotPage(200))]);

    app(BackfillWomSnapshots::class)->run($this->player);

    expect(snapshotRequests())->toHaveCount(BackfillWomSnapshots::MAX_PAGES);
});

it('keeps the last snapshot of each UTC day with every metric', function () {
    Http::fake(['api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response(womFixture('snapshots-bartheez'))]);

    expect(app(BackfillWomSnapshots::class)->run($this->player))->toBe(2);

    $days = WomDailySnapshot::where('account_id', $this->account->id)->orderBy('date')->get();

    // 2026-09-25's last snapshot is 23:59:09, not the first (06:04) of the day.
    expect($days->pluck('date')->all())->toBe(['2026-09-25', '2026-09-26'])
        ->and($days[0]->exp)->toBe(830354661)
        ->and($days[0]->ehp)->toEqualWithDelta(2512.5813, 0.001)
        ->and($days[0]->ehb)->toEqualWithDelta(1154.3622, 0.001)
        ->and($days[0]->skills)->toHaveCount(24)
        ->and($days[0]->bosses)->toHaveKey('zulrah')
        ->and($days[0]->activities)->toHaveKey('collections_logged')
        ->and($days[1]->exp)->toBe(832820305);
});

it('adds the maps to an existing headline-only day and writes one doc per day when run twice', function () {
    WomDailySnapshot::query()->insert(['account_id' => $this->account->id, 'date' => '2026-09-26', 'exp' => 5, 'ehp' => 1.0, 'ehb' => 1.0]);
    Http::fake(['api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response([womSnapshot(now()->subHour(), 1_000, zulrah: 42)])]);

    app(BackfillWomSnapshots::class)->run($this->player);
    app(BackfillWomSnapshots::class)->run($this->player);

    $day = WomDailySnapshot::where('account_id', $this->account->id)->sole();

    expect($day->exp)->toBe(1_000)
        ->and($day->skills)->toBe(['attack' => 41])
        ->and($day->bosses)->toBe(['zulrah' => 42])
        ->and($day->activities)->toBe(['clue_scrolls_all' => 5]);
});

it('omits unranked metrics, carries an unranked total and skips days before any total', function () {
    $unrankedTotal = womSnapshot(now()->subHour(), -1);
    $unrankedTotal['data']['bosses']['zulrah']['kills'] = -1;
    Http::fake(['api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response([
        $unrankedTotal,
        womSnapshot(now()->subDay(), 900),
        womSnapshot(now()->subDays(2), -1),
    ])]);

    expect(app(BackfillWomSnapshots::class)->run($this->player))->toBe(2);

    $days = WomDailySnapshot::where('account_id', $this->account->id)->orderBy('date')->get()->keyBy('date');

    expect($days->keys()->all())->toBe(['2026-09-25', '2026-09-26'])
        ->and($days['2026-09-26']->exp)->toBe(900)
        ->and($days['2026-09-26']->bosses)->toBe([]);
});

it('marks a player WOM does not know as backfilled', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404)]);

    expect(app(BackfillWomSnapshots::class)->run($this->player))->toBe(0)
        ->and($this->player->fresh()->backfilled_at)->not->toBeNull();
});
