<?php

use App\Jobs\SyncAccountCollectionLogJob;
use App\Jobs\SyncAccountHiscoresJob;
use App\Models\Account;
use App\Models\CollectionLog;
use App\Models\User;
use App\Services\CollectionLog\CollectionLogSync;
use App\Services\TempleOsrs\TempleOsrsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(fn () => CollectionLog::query()->delete());

function syncWith(array $responses): CollectionLogSync
{
    $client = new TempleOsrsClient(new Client(['handler' => HandlerStack::create(new MockHandler($responses))]));

    return new CollectionLogSync($client);
}

function clAccount(string $username = 'Iron Hyger'): Account
{
    return Account::factory()->for(User::factory()->withPersonalTeam())->create(['username' => $username]);
}

it('stores the collection log from TempleOSRS', function () {
    $sync = syncWith([
        new Response(200, [], json_encode(['data' => [
            'total_collections_finished' => 3,
            'total_collections_available' => 1701,
            'total_categories_finished' => 1,
            'total_categories_available' => 122,
            'items' => [
                'abyssal_sire' => [['id' => 13262, 'count' => 1, 'date' => '2022-03-20 20:02:40']],
            ],
        ]])),
    ]);

    $account = clAccount();
    $log = $sync->syncForAccount($account);

    expect($log)->not->toBeNull();
    expect($log->obtained)->toBe(3);
    expect($log->total)->toBe(1701);
    expect($log->items['abyssal_sire'][0]['id'])->toBe(13262);
    expect($log->fetched_at)->not->toBeNull();
});

it('returns null and stores nothing when the player has not synced on TempleOSRS', function () {
    $sync = syncWith([
        new Response(200, [], json_encode(['error' => ['Code' => 402, 'Message' => 'not synced']])),
    ]);

    $account = clAccount('Unsynced');

    expect($sync->syncForAccount($account))->toBeNull();
    expect(CollectionLog::where('account_id', $account->id)->exists())->toBeFalse();
});

it('upserts on a repeat sync', function () {
    $account = clAccount();

    syncWith([new Response(200, [], json_encode(['data' => ['total_collections_finished' => 1, 'items' => []]]))])
        ->syncForAccount($account);
    syncWith([new Response(200, [], json_encode(['data' => ['total_collections_finished' => 5, 'items' => []]]))])
        ->syncForAccount($account);

    expect(CollectionLog::where('account_id', $account->id)->count())->toBe(1);
    expect(CollectionLog::where('account_id', $account->id)->first()->obtained)->toBe(5);
});

it('queues both the hiscores and collection-log sync on the login trigger', function () {
    Queue::fake();
    $account = Account::factory()->for(User::factory()->withPersonalTeam())
        ->create(['username' => 'Zlimon', 'account_hash' => 'hash-cl']);
    Sanctum::actingAs($account->user);

    $this->putJson('/api/plugin/hiscores', [], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'hash-cl',
        'X-Account-Username' => 'Zlimon',
    ])->assertStatus(202);

    Queue::assertPushed(SyncAccountHiscoresJob::class);
    Queue::assertPushed(SyncAccountCollectionLogJob::class,
        fn (SyncAccountCollectionLogJob $job) => $job->accountId === $account->id);
});

it('ranks the collection-log leaderboard by slots unlocked', function () {
    $most = clAccount('Maxed');
    $few = clAccount('Starter');
    CollectionLog::create(['account_id' => $most->id, 'obtained' => 1200, 'total' => 1701, 'items' => []]);
    CollectionLog::create(['account_id' => $few->id, 'obtained' => 40, 'total' => 1701, 'items' => []]);

    $this->actingAs($most->user)
        ->get(route('hiscores.collection-log.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Hiscores/CollectionLog/Show')
            ->has('hiscores', 2)
            ->where('hiscores.0.account.username', 'Maxed')
            ->where('hiscores.0.obtained', 1200)
            ->where('hiscores.1.account.username', 'Starter'),
        );
});
