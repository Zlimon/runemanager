<?php

use App\Models\Account;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Services\WiseOldMan\WiseOldManClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAccountWithName(string $username): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Test',
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

function bindMockedWom(array $responses): void
{
    $mock = new MockHandler(array_map(
        fn (string $body) => new Response(200, ['Content-Type' => 'application/json'], $body),
        $responses
    ));
    $http = new HttpClient(['handler' => HandlerStack::create($mock)]);

    app()->instance(WiseOldManClient::class, new WiseOldManClient($http));
}

it('applies an approved rename to a tracked account', function () {
    $account = makeAccountWithName('PloughSheep');

    bindMockedWom([json_encode([
        ['oldName' => 'PloughSheep', 'newName' => 'Iron Sm1thyy', 'status' => 'approved'],
        ['oldName' => 'SomeOtherPlayer', 'newName' => 'NotOurAccount', 'status' => 'approved'],
    ])]);

    $this->artisan('name-changes:detect', ['--pages' => 1])
        ->expectsOutputToContain('Checked 2 WOM entries, matched 1 accounts, applied 1 renames.')
        ->assertSuccessful();

    expect($account->fresh()->username)->toBe('Iron Sm1thyy');
    expect(UsernameHistory::query()->count())->toBe(1);
    expect(UsernameHistory::query()->first()->old_username)->toBe('PloughSheep');
});

it('matches account names case-insensitively', function () {
    $account = makeAccountWithName('ploughsheep');

    bindMockedWom([json_encode([
        ['oldName' => 'PloughSheep', 'newName' => 'Iron Sm1thyy', 'status' => 'approved'],
    ])]);

    $this->artisan('name-changes:detect', ['--pages' => 1])->assertSuccessful();

    expect($account->fresh()->username)->toBe('Iron Sm1thyy');
});

it('ignores entries that do not match any tracked account', function () {
    makeAccountWithName('Zlimon');

    bindMockedWom([json_encode([
        ['oldName' => 'NobodyWeKnow', 'newName' => 'NewName', 'status' => 'approved'],
    ])]);

    $this->artisan('name-changes:detect', ['--pages' => 1])
        ->expectsOutputToContain('Checked 1 WOM entries, matched 0 accounts, applied 0 renames.')
        ->assertSuccessful();

    expect(UsernameHistory::query()->count())->toBe(0);
});

it('is idempotent on re-run (no double-history)', function () {
    $account = makeAccountWithName('First');

    bindMockedWom([
        json_encode([['oldName' => 'First', 'newName' => 'Second', 'status' => 'approved']]),
        json_encode([['oldName' => 'First', 'newName' => 'Second', 'status' => 'approved']]),
    ]);

    $this->artisan('name-changes:detect', ['--pages' => 1])->assertSuccessful();
    $this->artisan('name-changes:detect', ['--pages' => 1])->assertSuccessful();

    expect($account->fresh()->username)->toBe('Second');
    expect(UsernameHistory::query()->count())->toBe(1);
});

it('handles an empty WOM response gracefully', function () {
    makeAccountWithName('Zlimon');

    bindMockedWom([json_encode([])]);

    $this->artisan('name-changes:detect', ['--pages' => 1])
        ->expectsOutputToContain('Checked 0 WOM entries, matched 0 accounts, applied 0 renames.')
        ->assertSuccessful();
});

it('registers name-changes:detect to run hourly in the scheduler', function () {
    $events = collect(app(Schedule::class)->events());

    $event = $events->first(fn ($e) => str_contains($e->command ?? '', 'name-changes:detect'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 * * * *');
});
