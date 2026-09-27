<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\BackfillWomSeries;
use App\Services\WiseOldMan\SyncWomPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
    $this->travelTo(Carbon::parse('2026-09-26 12:00:00', 'UTC'));
});

/** Bartheez's real responses: the player (GET and POST) and three year timelines. */
function fakeBartheezOnWom(): void
{
    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=overall*' => Http::response(womFixture('timeline-bartheez-overall')),
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=ehp*' => Http::response(womFixture('timeline-bartheez-ehp')),
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=ehb*' => Http::response(womFixture('timeline-bartheez-ehb')),
        'api.wiseoldman.net/v2/players/bartheez' => Http::response(womFixture('player-bartheez')),
    ]);
}

function wasPosted(): bool
{
    return Http::recorded(fn (Request $request) => $request->method() === 'POST')->isNotEmpty();
}

it('links an account with a GET only when the owner has not opted in, even when stale', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 900_000_000, 'last_seen_at' => now()]);

    $player = app(SyncWomPlayer::class)->sync($account);

    expect(wasPosted())->toBeFalse()
        ->and($player->wom_player_id)->toBe(315771)
        ->and($player->type)->toBe('ironman')
        ->and($player->status)->toBe('active')
        ->and($player->exp)->toBe(832820305)
        ->and($player->ehp)->toEqualWithDelta(2513.2407, 0.001)
        ->and($player->ehb)->toEqualWithDelta(1158.8955, 0.001)
        ->and($player->combat_level)->toBe(126)
        ->and($player->tracking_opt_in)->toBeFalse()
        ->and($player->show_on_progress)->toBeTrue()
        ->and($player->last_changed_at->toIso8601String())->toBe('2026-09-26T06:19:24+00:00')
        ->and($player->update_requested_at)->toBeNull()
        ->and($player->backfilled_at)->not->toBeNull();
});

it('downsamples the backfill to the last value of each UTC day', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez']);

    app(SyncWomPlayer::class)->sync($account);

    $days = WomDailySnapshot::where('account_id', $account->id)->orderBy('date')->get();

    expect($days->pluck('date')->all())->toBe(['2026-09-25', '2026-09-26']);

    // 2026-09-25's last snapshot is 23:59:09, not the first (06:04) of the day.
    expect($days[0]->exp)->toBe(830354661)
        ->and($days[0]->ehp)->toEqualWithDelta(2512.5813, 0.001)
        ->and($days[0]->ehb)->toEqualWithDelta(1154.3622, 0.001)
        ->and($days[1]->exp)->toBe(832820305)
        ->and($days[1]->ehb)->toEqualWithDelta(1158.8955, 0.001);
});

it('does not backfill twice', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez']);
    WomPlayer::factory()->for($account)->create(['backfilled_at' => now()->subDay()]);

    app(SyncWomPlayer::class)->sync($account->fresh());

    Http::assertSentCount(1);
    expect(WomDailySnapshot::where('account_id', $account->id)->count())->toBe(1);
});

it('asks WOM to update an opted-in player whose data is behind', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 900_000_000]);
    WomPlayer::factory()->for($account)->create([
        'tracking_opt_in' => true,
        'exp' => 800_000_000,
        'update_requested_at' => null,
    ]);

    $player = app(SyncWomPlayer::class)->sync($account->fresh());

    Http::assertSentCount(1);
    expect(wasPosted())->toBeTrue()
        ->and($player->update_requested_at->equalTo(now()))->toBeTrue();
});

it('treats a plugin sighting after WOM\'s last update as stale', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 0, 'last_seen_at' => now()]);
    WomPlayer::factory()->for($account)->create(['tracking_opt_in' => true, 'wom_updated_at' => now()->subDay()]);

    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(wasPosted())->toBeTrue();
});

it('only reads an opted-in player whose data is current', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 1, 'last_seen_at' => now()->subDays(2)]);
    WomPlayer::factory()->for($account)->create(['tracking_opt_in' => true, 'exp' => 5, 'wom_updated_at' => now()->subDay()]);

    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(wasPosted())->toBeFalse();
});

it('does not ask WOM to update more than once every six hours', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 900_000_000]);
    WomPlayer::factory()->for($account)->create([
        'tracking_opt_in' => true,
        'exp' => 800_000_000,
        'update_requested_at' => now()->subHours(2),
    ]);

    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(wasPosted())->toBeFalse();

    $this->travel(5)->hours();
    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(wasPosted())->toBeTrue();
});

it('records a player WOM does not know and waits a week before looking again', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404)]);
    $account = Account::factory()->create(['username' => 'Nobody Here']);

    $player = app(SyncWomPlayer::class)->sync($account);

    expect($player->exists)->toBeTrue()
        ->and($player->isLinked())->toBeFalse()
        ->and($player->not_found_at->equalTo(now()))->toBeTrue()
        ->and(wasPosted())->toBeFalse();
    Http::assertSentCount(1);

    $this->travel(6)->days();
    app(SyncWomPlayer::class)->sync($account->fresh());
    Http::assertSentCount(1);

    $this->travel(2)->days();
    app(SyncWomPlayer::class)->sync($account->fresh());
    Http::assertSentCount(2);
    expect(WomDailySnapshot::where('account_id', $account->id)->count())->toBe(0);
});

it('POSTs on an owner-requested update even when WOM is current, but only when opted in', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez', 'xp' => 1, 'last_seen_at' => null]);
    $player = WomPlayer::factory()->for($account)->create(['tracking_opt_in' => false, 'exp' => 5]);

    app(SyncWomPlayer::class)->sync($account->fresh(), requestUpdate: true);
    expect(wasPosted())->toBeFalse();

    $player->update(['tracking_opt_in' => true]);
    app(SyncWomPlayer::class)->sync($account->fresh(), requestUpdate: true);
    expect(wasPosted())->toBeTrue();
});

it('never stores an unranked -1 total as a real value', function () {
    $details = womFixture('player-bartheez');
    $details['exp'] = -1;
    $details['ehb'] = -1;
    Http::fake(['api.wiseoldman.net/v2/players/bartheez' => Http::response($details)]);
    $account = Account::factory()->create(['username' => 'Bartheez']);
    $player = WomPlayer::factory()->for($account)->create(['exp' => 700_000_000, 'ehb' => 1000.5]);

    app(SyncWomPlayer::class)->sync($account->fresh());

    expect($player->fresh()->exp)->toBe(700_000_000)
        ->and($player->fresh()->ehb)->toBe(1000.5)
        ->and(WomDailySnapshot::where('account_id', $account->id)->count())->toBe(0);
});

it('skips unranked -1 points in the backfill and carries the day before', function () {
    $overall = womFixture('timeline-bartheez-overall');
    $ehb = womFixture('timeline-bartheez-ehb');
    // The latest point of 2026-09-26 is unranked, and so is every ehb point
    // that day; earlier valid points and the previous day must fill in.
    array_unshift($overall, ['value' => -1, 'rank' => -1, 'date' => '2026-09-26T11:00:00.000Z']);
    $ehb = array_map(fn (array $point) => str_starts_with($point['date'], '2026-09-26') ? ['value' => -1] + $point : $point, $ehb);
    // A lone unranked day before any valid point is left out entirely.
    $overall[] = ['value' => -1, 'rank' => -1, 'date' => '2026-09-20T12:00:00.000Z'];

    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=overall*' => Http::response($overall),
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=ehp*' => Http::response(womFixture('timeline-bartheez-ehp')),
        'api.wiseoldman.net/v2/players/bartheez/snapshots/timeline?metric=ehb*' => Http::response($ehb),
    ]);
    $account = Account::factory()->create(['username' => 'Bartheez']);
    $player = WomPlayer::factory()->for($account)->create(['backfilled_at' => null]);

    $written = app(BackfillWomSeries::class)->run($player);

    $days = WomDailySnapshot::where('account_id', $account->id)->orderBy('date')->get()->keyBy('date');

    expect($written)->toBe(2)
        ->and($days->keys()->all())->toBe(['2026-09-25', '2026-09-26'])
        ->and($days['2026-09-26']->exp)->toBe(832820305)
        ->and($days['2026-09-26']->ehb)->toEqualWithDelta(1154.3622, 0.001);
});

it('writes one point per account and day, even when the backfill runs twice', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez']);
    $player = WomPlayer::factory()->for($account)->create();

    app(BackfillWomSeries::class)->run($player);
    app(BackfillWomSeries::class)->run($player);

    expect(WomDailySnapshot::where('account_id', $account->id)->count())->toBe(2);
});
