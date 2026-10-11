<?php

use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\StartWomTracking;
use App\Services\WiseOldMan\StopWomTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Http::preventStrayRequests();
    Queue::fake();
});

it('opts a new account in and queues the update without any HTTP', function () {
    $account = Account::factory()->create(['username' => 'Bartheez']);

    $player = app(StartWomTracking::class)->start($account);

    expect($player->exists)->toBeTrue()
        ->and($player->fresh()->tracking_opt_in)->toBeTrue()
        ->and(WomPlayer::count())->toBe(1);

    Http::assertNothingSent();
    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->accountId === $account->id && $job->requestUpdate);
});

it('opts in an account that already has a row without creating another', function () {
    $account = Account::factory()->create();
    $existing = WomPlayer::factory()->notFound()->for($account)->create();

    $player = app(StartWomTracking::class)->start($account);

    expect($player->is($existing))->toBeTrue()
        ->and($existing->fresh()->tracking_opt_in)->toBeTrue()
        ->and(WomPlayer::count())->toBe(1);
});

it('withdraws the opt-in without touching the rest of the row', function () {
    $account = Account::factory()->create();
    $player = WomPlayer::factory()->for($account)->create(['tracking_opt_in' => true]);

    app(StopWomTracking::class)->stop($account);

    expect($player->fresh()->tracking_opt_in)->toBeFalse()
        ->and($player->fresh()->isLinked())->toBeTrue();
    Queue::assertNothingPushed();
    Http::assertNothingSent();
});

it('does nothing when stopping an account that was never linked', function () {
    $account = Account::factory()->create();

    expect(app(StopWomTracking::class)->stop($account))->toBeNull()
        ->and(WomPlayer::count())->toBe(0);
});
