<?php

use App\Jobs\SyncAccountHiscoresJob;
use App\Models\Account;
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
