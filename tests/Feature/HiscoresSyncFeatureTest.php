<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\User;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Hiscores\HiscoresSync;
use App\Services\Hiscores\OsrsHiscoresClient;
use GuzzleHttp\Client;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMockedSync(array $payload): HiscoresSync
{
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode($payload)),
    ]);
    $http = new HttpClient(['handler' => HandlerStack::create($mock)]);

    return new HiscoresSync(new OsrsHiscoresClient($http), new RecordFeedEvent);
}

function makeAccount(string $username = 'Zlimon'): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Test User',
        'email' => $username.'@test.local',
        'password' => bcrypt('password'),
        'icon_id' => 0,
    ]);

    return Account::query()->forceCreate([
        'user_id' => $user->id,
        'account_type' => 'normal',
        'username' => $username,
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);
}

it('denormalises the overall entry onto the parent Account', function () {
    $account = makeAccount();
    expect($account->level)->toBe(0)->and($account->rank)->toBe(0)->and($account->xp)->toBe(0);

    $sync = makeMockedSync([
        'skills' => [
            ['id' => 0, 'name' => 'Overall', 'rank' => 12345, 'level' => 1500, 'xp' => 99_999_999],
            ['id' => 1, 'name' => 'Attack', 'rank' => 500, 'level' => 99, 'xp' => 200_000_000],
        ],
        'activities' => [],
    ]);

    $sync->syncForAccount($account);

    $account->refresh();
    expect($account->rank)->toBe(12345)
        ->and($account->level)->toBe(1500)
        ->and($account->xp)->toBe(99_999_999);
});

it('leaves Account stats untouched when no overall entry is returned', function () {
    $account = makeAccount();
    $account->forceFill(['rank' => 42, 'level' => 1500, 'xp' => 999])->save();

    $sync = makeMockedSync([
        'skills' => [
            ['id' => 1, 'name' => 'Attack', 'rank' => 500, 'level' => 99, 'xp' => 200_000_000],
        ],
        'activities' => [],
    ]);

    $sync->syncForAccount($account);

    $account->refresh();
    expect($account->rank)->toBe(42)
        ->and($account->level)->toBe(1500)
        ->and($account->xp)->toBe(999);
});

it('upserts an AccountHiscore row on first sync', function () {
    $account = makeAccount();
    $sync = makeMockedSync([
        'skills' => [
            ['id' => 1, 'name' => 'Attack', 'rank' => 500, 'level' => 99, 'xp' => 200_000_000],
        ],
        'activities' => [
            ['id' => 1, 'name' => 'Zulrah', 'rank' => 42, 'score' => 1234],
        ],
    ]);

    $hiscore = $sync->syncForAccount($account);

    expect($hiscore->account_id)->toBe($account->id);
    expect($hiscore->entries['skills']['attack'])->toBe(['rank' => 500, 'level' => 99, 'xp' => 200_000_000]);
    expect($hiscore->entries['activities']['zulrah'])->toBe(['rank' => 42, 'score' => 1234]);
    expect(AccountHiscore::query()->count())->toBe(1);
});

it('overwrites the existing row on subsequent sync (one row per account)', function () {
    $account = makeAccount();

    makeMockedSync([
        'skills' => [['id' => 1, 'name' => 'Attack', 'rank' => 1000, 'level' => 50, 'xp' => 100_000]],
        'activities' => [],
    ])->syncForAccount($account);

    $second = makeMockedSync([
        'skills' => [['id' => 1, 'name' => 'Attack', 'rank' => 500, 'level' => 99, 'xp' => 200_000_000]],
        'activities' => [],
    ])->syncForAccount($account);

    expect(AccountHiscore::query()->count())->toBe(1);
    expect($second->entries['skills']['attack']['level'])->toBe(99);
});

it('throws on a malformed payload', function () {
    $account = makeAccount();
    $sync = makeMockedSync(['unexpected' => 'shape']);

    $sync->syncForAccount($account);
})->throws(RuntimeException::class);

it('hiscores:sync command iterates every account when no username given', function () {
    $a = makeAccount('Alpha');
    $b = makeAccount('Bravo');

    $payload = json_encode([
        'skills' => [['id' => 1, 'name' => 'Attack', 'rank' => 1, 'level' => 99, 'xp' => 1]],
        'activities' => [],
    ]);
    $mock = new MockHandler([
        new Response(200, [], $payload),
        new Response(200, [], $payload),
    ]);
    $http = new Client(['handler' => HandlerStack::create($mock)]);
    $this->app->instance(OsrsHiscoresClient::class, new OsrsHiscoresClient($http));

    $this->artisan('hiscores:sync')
        ->expectsOutputToContain('Alpha')
        ->expectsOutputToContain('Bravo')
        ->expectsOutputToContain('Synced 2, failed 0.')
        ->assertSuccessful();

    expect(AccountHiscore::query()->count())->toBe(2);
});

it('keeps going past a failed account', function () {
    makeAccount('Good1');
    makeAccount('BadAccount');
    makeAccount('Good2');

    $okBody = json_encode(['skills' => [], 'activities' => []]);
    $mock = new MockHandler([
        new Response(200, [], $okBody),
        new ConnectException(
            'Network is down',
            new Request('GET', 'index_lite.json')
        ),
        new Response(200, [], $okBody),
    ]);
    $http = new Client(['handler' => HandlerStack::create($mock)]);
    $this->app->instance(OsrsHiscoresClient::class, new OsrsHiscoresClient($http));

    $this->artisan('hiscores:sync')
        ->expectsOutputToContain('BadAccount')
        ->expectsOutputToContain('Synced 2, failed 1.')
        ->assertSuccessful();

    expect(AccountHiscore::query()->count())->toBe(2);
});

it('registers hiscores:sync to run hourly in the scheduler', function () {
    $events = collect(app(Schedule::class)->events());

    $hiscoreEvent = $events->first(fn ($e) => str_contains($e->command ?? '', 'hiscores:sync'));

    expect($hiscoreEvent)->not->toBeNull();
    expect($hiscoreEvent->expression)->toBe('0 * * * *');
});
