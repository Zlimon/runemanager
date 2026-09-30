<?php

use App\Jobs\SyncAccountCollectionLogJob;
use App\Jobs\SyncAccountHiscoresJob;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\CollectionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function hiscoreHeaders(string $hash = 'hash-hs', string $username = 'Zlimon'): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('queues a hiscores sync for the resolved account', function () {
    Queue::fake();

    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-hs']);
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/hiscores', [], hiscoreHeaders())
        ->assertStatus(202)
        ->assertJsonPath('data.queued', true);

    Queue::assertPushed(
        SyncAccountHiscoresJob::class,
        fn (SyncAccountHiscoresJob $job) => $job->accountId === $account->id,
    );
});

it('requires authentication', function () {
    Queue::fake();

    $this->putJson('/api/plugin/hiscores', [], ['Accept' => 'application/json'])
        ->assertUnauthorized();

    Queue::assertNothingPushed();
});

it('queues one fetch per account while it is pending, however often the plugin asks', function () {
    Queue::fake();

    $user = User::factory()->withPersonalTeam()->create();
    Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-hs']);
    Sanctum::actingAs($user);

    foreach (range(1, 3) as $attempt) {
        $this->putJson('/api/plugin/hiscores', [], hiscoreHeaders())
            ->assertStatus(202)
            ->assertJsonPath('data.queued', true);
    }

    Queue::assertPushed(SyncAccountHiscoresJob::class, 1);
    Queue::assertPushed(SyncAccountCollectionLogJob::class, 1);
});

it('skips a fetch the account had within the cooldown, but not an older one', function () {
    Queue::fake();

    $user = User::factory()->withPersonalTeam()->create();
    $account = Account::factory()->for($user)->create(['username' => 'Zlimon', 'account_hash' => 'hash-hs']);
    Sanctum::actingAs($user);

    AccountHiscore::query()->create(['account_id' => $account->id, 'entries' => [], 'fetched_at' => now()->subMinute()]);
    (new CollectionLog)->forceFill(['account_id' => $account->id, 'fetched_at' => now()->subHour()])->save();

    $this->putJson('/api/plugin/hiscores', [], hiscoreHeaders())
        ->assertStatus(202)
        ->assertJsonPath('data.queued', true);

    Queue::assertNotPushed(SyncAccountHiscoresJob::class);
    Queue::assertPushed(SyncAccountCollectionLogJob::class, 1);
});
