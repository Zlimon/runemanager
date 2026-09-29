<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
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

/**
 * Bartheez's real responses: the player (GET and POST), a page of snapshots,
 * achievements and records.
 */
function fakeBartheezOnWom(): void
{
    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/snapshots*' => Http::response(womFixture('snapshots-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/achievements' => Http::response(womFixture('achievements-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/records' => Http::response(womFixture('records-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez' => Http::response(womFixture('player-bartheez')),
    ]);
}

function milestonesFetched(): bool
{
    return Http::recorded(fn (Request $request) => str_ends_with($request->url(), '/achievements'))->isNotEmpty();
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

it('stores every ranked metric of the latest snapshot on today\'s document', function () {
    $details = womFixture('player-bartheez');
    $details['latestSnapshot']['data']['bosses']['zulrah']['kills'] = -1;
    $details['latestSnapshot']['data']['activities']['pvp_arena']['score'] = -1;
    $details['latestSnapshot']['data']['bosses']['bryophyta']['kills'] = 0;
    $account = Account::factory()->create(['username' => 'Bartheez']);
    $player = WomPlayer::factory()->for($account)->create();

    app(SyncWomPlayer::class)->apply($player, $details);

    $day = WomDailySnapshot::where('account_id', $account->id)->sole();

    expect($day->date)->toBe('2026-09-26')
        ->and($day->exp)->toBe(832820305)
        ->and($day->skills)->toHaveCount(24)
        ->and($day->skills)->not->toHaveKey('overall')
        ->and($day->skills['attack'])->toBe(41239184)
        ->and($day->skills)->toHaveKey('runecrafting')
        ->and($day->bosses)->not->toHaveKey('zulrah')
        ->and($day->bosses['bryophyta'])->toBe(0)
        ->and($day->bosses['vorkath'])->toBeInt()
        ->and($day->activities)->not->toHaveKey('pvp_arena')
        ->and($day->activities['clue_scrolls_all'])->toBe(1684);
});

it('keeps a day\'s maps when a payload has no latest snapshot', function () {
    $account = Account::factory()->create(['username' => 'Bartheez']);
    $player = WomPlayer::factory()->for($account)->create();
    WomDailySnapshot::query()->insert(['account_id' => $account->id, 'date' => '2026-09-26', 'exp' => 1, 'ehp' => 0.0, 'ehb' => 0.0, 'skills' => ['attack' => 5]]);
    $details = womFixture('player-bartheez');
    unset($details['latestSnapshot']);

    app(SyncWomPlayer::class)->apply($player, $details);

    $day = WomDailySnapshot::where('account_id', $account->id)->sole();

    expect($day->exp)->toBe(832820305)
        ->and($day->skills)->toBe(['attack' => 5]);
});

it('syncs achievements and records on first link', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez']);

    $player = app(SyncWomPlayer::class)->sync($account);

    expect(milestonesFetched())->toBeTrue()
        ->and($player->milestones_synced_at->equalTo(now()))->toBeTrue()
        ->and($account->womAchievements()->count())->toBeGreaterThan(0)
        ->and($account->womRecords()->count())->toBeGreaterThan(0);
});

it('syncs achievements and records only when WOM saw a change since the last run', function () {
    fakeBartheezOnWom();
    $account = Account::factory()->create(['username' => 'Bartheez']);
    // The fixture's lastChangedAt is 2026-09-26 06:19.
    $player = WomPlayer::factory()->for($account)->create(['milestones_synced_at' => '2026-09-26 07:00:00']);

    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(milestonesFetched())->toBeFalse()
        ->and($player->fresh()->milestones_synced_at->toDateTimeString())->toBe('2026-09-26 07:00:00');

    $player->update(['milestones_synced_at' => '2026-09-26 06:00:00']);
    app(SyncWomPlayer::class)->sync($account->fresh());

    expect(milestonesFetched())->toBeTrue()
        ->and($player->fresh()->milestones_synced_at->equalTo(now()))->toBeTrue();
});

it('never syncs milestones for a player WOM does not know', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Player not found.'], 404)]);
    $account = Account::factory()->create(['username' => 'Nobody Here']);

    app(SyncWomPlayer::class)->sync($account);

    Http::assertSentCount(1);
});
