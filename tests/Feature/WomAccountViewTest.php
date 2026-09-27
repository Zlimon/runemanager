<?php

use App\Models\Account;
use App\Models\WomDailySnapshot;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\WiseOldManClient;
use App\Services\WiseOldMan\WomAccountView;
use App\Services\WiseOldMan\WomSnapshotHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    RateLimiter::clear('wiseoldman:requests');
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
});

function womAccountDay(Account $account, int $daysAgo, int $exp, float $ehp = 0, float $ehb = 0): void
{
    WomDailySnapshot::query()->create([
        'account_id' => $account->id,
        'date' => now('UTC')->subDays($daysAgo)->toDateString(),
        'exp' => $exp,
        'ehp' => $ehp,
        'ehb' => $ehb,
    ]);
}

it('has no history for an unlinked account', function () {
    $account = Account::factory()->create();
    WomPlayer::factory()->notFound()->for($account)->create();
    womAccountDay($account, 1, 100);

    expect(app(WomAccountView::class)->series($account, 'all'))->toBe([]);
});

it('has no summary before the account was ever looked up', function () {
    expect(app(WomAccountView::class)->summary(Account::factory()->create()))->toBeNull();
});

it('summarises a linked profile with its wiseoldman.net link', function () {
    $account = Account::factory()->create(['username' => 'Iron Bart']);
    WomPlayer::factory()->for($account)->create(['ehp' => 812.5, 'status' => 'active']);

    $summary = app(WomAccountView::class)->summary($account);

    expect($summary['linked'])->toBeTrue()
        ->and($summary['not_found'])->toBeFalse()
        ->and($summary['ehp'])->toBe(812.5)
        ->and($summary['url'])->toBe('https://wiseoldman.net/players/iron%20bart');
});

it('marks a profile WOM does not know as not found, without a link', function () {
    $account = Account::factory()->create();
    WomPlayer::factory()->notFound()->for($account)->create();

    $summary = app(WomAccountView::class)->summary($account);

    expect($summary['linked'])->toBeFalse()
        ->and($summary['not_found'])->toBeTrue()
        ->and($summary['url'])->toBeNull();
});

it('returns the range of daily points oldest first as millisecond timestamps', function () {
    $account = Account::factory()->create();
    WomPlayer::factory()->for($account)->create();
    womAccountDay($account, 40, 100);
    womAccountDay($account, 3, 300, 2.5, 1.0);
    womAccountDay($account, 5, 200);

    $series = app(WomAccountView::class)->series($account, 'month');

    expect($series)->toHaveCount(2)
        ->and(array_column($series, 'exp'))->toBe([200, 300])
        ->and($series[1])->toBe([
            't' => now('UTC')->subDays(3)->startOfDay()->getTimestampMs(),
            'exp' => 300,
            'ehp' => 2.5,
            'ehb' => 1.0,
        ])
        ->and(app(WomAccountView::class)->series($account, 'all'))->toHaveCount(3);
});

it('thins long ranges to at most the point budget, keeping both ends', function () {
    $points = range(1, 1000);

    $thinned = app(WomSnapshotHistory::class)->downsample($points);

    expect(count($thinned))->toBeLessThanOrEqual(WomSnapshotHistory::MAX_POINTS)
        ->and($thinned[0])->toBe(1)
        ->and(end($thinned))->toBe(1000);
});

it('shapes gains: headline from history, breakdown from WOM live, cached', function () {
    Http::fake(['api.wiseoldman.net/v2/players/meekss/gained*' => Http::response(womFixture('gained-meekss-week'))]);

    $account = Account::factory()->create(['username' => 'Meekss']);
    WomPlayer::factory()->for($account)->create();
    womAccountDay($account, 10, 1_000, 1.0, 2.0);
    womAccountDay($account, 7, 1_500, 1.5, 2.0);
    womAccountDay($account, 0, 4_000, 4.0, 3.5);

    $gains = app(WomAccountView::class)->gains($account, 'week');
    app(WomAccountView::class)->gains($account, 'week');

    expect($gains['headline'])->toBe(['exp' => 2_500, 'ehp' => 2.5, 'ehb' => 1.5])
        ->and($gains['skills'])->toBe([
            ['metric' => 'slayer', 'gained' => 5_831_764, 'level' => $gains['skills'][0]['level']],
            ['metric' => 'attack', 'gained' => 42_015, 'level' => 99],
        ])
        ->and($gains['bosses'])->each->toHaveKeys(['metric', 'gained']);

    Http::assertSentCount(1);
});

it('falls back to an empty breakdown when WOM is rate-limited', function () {
    Http::fake(['api.wiseoldman.net/*' => Http::response([], 429, ['Retry-After' => '30'])]);

    $account = Account::factory()->create();
    WomPlayer::factory()->for($account)->create();
    womAccountDay($account, 0, 4_000);

    $gains = app(WomAccountView::class)->gains($account, 'day');

    expect($gains['skills'])->toBe([])
        ->and($gains['bosses'])->toBe([])
        ->and($gains['headline']['exp'])->toBe(0);
});

it('falls back to an empty, uncached breakdown when WOM times out', function () {
    Http::fakeSequence('api.wiseoldman.net/v2/players/meekss/gained*')
        ->pushFailedConnection()
        ->push(womFixture('gained-meekss-week'));

    $account = Account::factory()->create(['username' => 'Meekss']);
    WomPlayer::factory()->for($account)->create();

    expect(app(WomAccountView::class)->gains($account, 'week')['skills'])->toBe([])
        ->and(app(WomAccountView::class)->gains($account, 'week')['skills'])->not->toBe([]);

    Http::assertSentCount(2);
});

it('asks WOM for the breakdown with the interactive timeout', function () {
    $client = Mockery::mock(WiseOldManClient::class);
    $interactive = Mockery::mock(WiseOldManClient::class);
    $client->shouldReceive('interactive')->once()->andReturn($interactive);
    $interactive->shouldReceive('gained')->once()->with('Meekss', 'week')->andReturn(womFixture('gained-meekss-week'));
    app()->instance(WiseOldManClient::class, $client);

    $account = Account::factory()->create(['username' => 'Meekss']);
    WomPlayer::factory()->for($account)->create();

    expect(app(WomAccountView::class)->gains($account, 'week')['skills'][0]['metric'])->toBe('slayer');
});

it('never asks WOM for the breakdown of an unlinked account', function () {
    Http::fake();

    $account = Account::factory()->create();
    WomPlayer::factory()->notFound()->for($account)->create();

    expect(app(WomAccountView::class)->gains($account, 'week'))
        ->toBe(['headline' => ['exp' => 0, 'ehp' => 0.0, 'ehb' => 0.0], 'skills' => [], 'bosses' => []]);

    Http::assertNothingSent();
});
