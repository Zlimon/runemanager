<?php

use App\Models\WomAchievement;
use App\Models\WomPlayer;
use App\Models\WomRecord;
use App\Services\WiseOldMan\SyncWomMilestones;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
    $this->travelTo(Carbon::parse('2026-09-28 06:00:00', 'UTC'));
});

/**
 * @param  array<int, array<string, mixed>>|null  $achievements
 */
function fakeBartheezMilestones(?array $achievements = null): void
{
    Http::fake([
        'api.wiseoldman.net/v2/players/bartheez/achievements' => Http::response($achievements ?? womFixture('achievements-bartheez')),
        'api.wiseoldman.net/v2/players/bartheez/records' => Http::response(womFixture('records-bartheez')),
    ]);
}

it('stores achievements with unknown dates as null and legacy flags', function () {
    fakeBartheezMilestones();
    $player = WomPlayer::factory()->create(['milestones_synced_at' => null]);

    app(SyncWomMilestones::class)->sync($player, 'Bartheez');

    expect(WomAchievement::where('account_id', $player->account_id)->count())->toBe(16);

    $undated = WomAchievement::where('name', '99 Strength')->sole();
    expect($undated->achieved_at)->toBeNull()
        ->and($undated->accuracy)->toBeNull()
        ->and($undated->threshold)->toBe(13_034_431);

    $sailing = WomAchievement::where('name', '99 Sailing')->sole();
    expect($sailing->achieved_at->toIso8601String())->toBe('2025-12-28T05:24:45+00:00')
        ->and($sailing->accuracy)->toBe(24463878)
        ->and($sailing->legacy)->toBeFalse();

    expect(WomAchievement::where('legacy', true)->pluck('name')->sort()->values()->all())
        ->toBe(['Base 80 Stats (Pre-Sailing)', 'Base 90 Stats (Pre-Sailing)']);

    expect($player->fresh()->milestones_synced_at->equalTo(now()))->toBeTrue();
});

it('stores day to year records to the minute and skips five-minute ones', function () {
    fakeBartheezMilestones();
    $player = WomPlayer::factory()->create();

    app(SyncWomMilestones::class)->sync($player, 'Bartheez');

    expect(WomRecord::count())->toBe(32)
        ->and(WomRecord::where('period', 'five_min')->exists())->toBeFalse();

    $record = WomRecord::where(['period' => 'year', 'metric' => 'strength'])->sole();
    expect($record->value)->toBe(46010468.0)
        ->and($record->achieved_at->toIso8601String())->toBe('2026-09-27T20:03:00+00:00');
});

it('updates rows in place and drops achievements WOM no longer returns', function () {
    $player = WomPlayer::factory()->create();
    $gone = WomAchievement::factory()->for($player->account)->create(['name' => 'Renamed achievement']);
    WomRecord::factory()->for($player->account)->create(['period' => 'year', 'metric' => 'strength', 'value' => 1]);

    fakeBartheezMilestones();
    app(SyncWomMilestones::class)->sync($player, 'Bartheez');
    app(SyncWomMilestones::class)->sync($player, 'Bartheez');

    expect(WomAchievement::whereKey($gone->id)->exists())->toBeFalse()
        ->and(WomAchievement::count())->toBe(16)
        ->and(WomRecord::count())->toBe(32)
        ->and(WomRecord::where(['period' => 'year', 'metric' => 'strength'])->value('value'))->toBe(46010468.0);
});

it('keeps stored achievements when WOM returns none', function () {
    $player = WomPlayer::factory()->create();
    WomAchievement::factory()->for($player->account)->create();

    fakeBartheezMilestones([]);
    app(SyncWomMilestones::class)->sync($player, 'Bartheez');

    expect(WomAchievement::count())->toBe(1);
});
