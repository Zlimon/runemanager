<?php

use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\User;
use App\Services\Hiscores\HiscoresSync;
use App\Services\Hiscores\OsrsHiscoresClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMockedSync(array $payload): HiscoresSync
{
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode($payload)),
    ]);
    $http = new HttpClient(['handler' => HandlerStack::create($mock)]);

    return new HiscoresSync(new OsrsHiscoresClient($http));
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
