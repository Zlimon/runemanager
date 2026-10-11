<?php

use App\Helpers\SettingHelper;
use App\Jobs\SyncWomPlayerJob;
use App\Models\Account;
use App\Models\User;
use App\Models\WomPlayer;
use App\Services\WiseOldMan\SyncWomPlayer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Http::preventStrayRequests();
    config(['services.wiseoldman.url' => 'https://api.wiseoldman.net/v2', 'services.wiseoldman.api_key' => null]);
});

it('does nothing while the integration is disabled', function () {
    Queue::fake();
    Account::factory()->count(2)->create();

    $this->artisan('wom:sync')
        ->expectsOutput('The Wise Old Man integration is disabled.')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

it('queues one job per account when enabled', function () {
    Queue::fake();
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $accounts = Account::factory()->count(3)->create();

    $this->artisan('wom:sync')->expectsOutput('Queued 3.')->assertSuccessful();

    Queue::assertPushed(SyncWomPlayerJob::class, 3);
    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->accountId === $accounts[0]->id);
});

it('queues a single account by username and fails on an unknown one', function () {
    Queue::fake();
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $account = Account::factory()->create(['username' => 'Bartheez']);
    Account::factory()->create();

    $this->artisan('wom:sync Bartheez')->assertSuccessful();
    Queue::assertPushed(SyncWomPlayerJob::class, 1);
    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->accountId === $account->id);

    $this->artisan('wom:sync Nobody')->assertFailed();
});

it('makes the job a no-op once the integration is turned off', function () {
    $account = Account::factory()->create(['username' => 'Bartheez']);

    (new SyncWomPlayerJob($account->id))->handle(app(SyncWomPlayer::class));

    Http::assertNothingSent();
    expect(WomPlayer::count())->toBe(0);
});

it('releases the job for the Retry-After delay when WOM rate-limits it', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    Http::fake(['api.wiseoldman.net/*' => Http::response([], 429, ['Retry-After' => '30'])]);
    $account = Account::factory()->create(['username' => 'Bartheez']);

    $job = (new SyncWomPlayerJob($account->id))->withFakeQueueInteractions();
    $job->handle(app(SyncWomPlayer::class));

    $job->assertReleased(delay: 30);
});

it('gives up quietly on a 4xx that a retry would not fix', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    Http::fake(['api.wiseoldman.net/*' => Http::response(['message' => 'Bad request'], 422)]);
    $account = Account::factory()->create(['username' => 'Bartheez']);

    $job = (new SyncWomPlayerJob($account->id))->withFakeQueueInteractions();
    $job->handle(app(SyncWomPlayer::class));

    $job->assertNotReleased();
    $job->assertNotFailed();
});

it('queues a delayed WOM sync on the plugin login snapshot only when enabled', function () {
    Queue::fake();
    $user = User::factory()->withPersonalTeam()->create();
    Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-wom']);
    Sanctum::actingAs($user);
    $headers = ['Accept' => 'application/json', 'X-Account-Hash' => 'hash-wom', 'X-Account-Username' => 'Zlimon'];

    $this->putJson('/api/plugin/hiscores', [], $headers)->assertStatus(202);
    Queue::assertNotPushed(SyncWomPlayerJob::class);

    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $this->putJson('/api/plugin/hiscores', [], $headers)->assertStatus(202);
    Queue::assertPushed(SyncWomPlayerJob::class, fn (SyncWomPlayerJob $job) => $job->delay !== null);
});

it('skips the plugin-triggered WOM sync when the last one is under an hour old', function () {
    Queue::fake();
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-wom']);
    $player = WomPlayer::factory()->for($account)->create(['synced_at' => now()->subMinutes(20)]);
    Sanctum::actingAs($user);
    $headers = ['Accept' => 'application/json', 'X-Account-Hash' => 'hash-wom', 'X-Account-Username' => 'Zlimon'];

    $this->putJson('/api/plugin/hiscores', [], $headers)->assertStatus(202);
    Queue::assertNotPushed(SyncWomPlayerJob::class);

    $player->update(['synced_at' => now()->subHours(2)]);
    $this->putJson('/api/plugin/hiscores', [], $headers)->assertStatus(202);
    Queue::assertPushed(SyncWomPlayerJob::class);
});

it('is unique per account and kind, and passes an owner request through', function () {
    SettingHelper::setSetting('wom_enabled', true, 'bool');
    Http::fake(['api.wiseoldman.net/v2/players/bartheez' => Http::response(womFixture('player-bartheez'))]);
    $account = Account::factory()->create(['username' => 'Bartheez']);
    WomPlayer::factory()->for($account)->create(['tracking_opt_in' => true, 'exp' => 900_000_000]);

    expect((new SyncWomPlayerJob($account->id))->uniqueId())->toBe((string) $account->id)
        ->and((new SyncWomPlayerJob($account->id, requestUpdate: true))->uniqueId())->toBe($account->id.':update')
        ->and((new SyncWomPlayerJob($account->id))->uniqueFor)->toBe(21600);

    (new SyncWomPlayerJob($account->id, requestUpdate: true))->handle(app(SyncWomPlayer::class));

    Http::assertSent(fn ($request) => $request->method() === 'POST');
});
