<?php

use App\Models\Account;
use App\Models\Inventory;
use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // RefreshDatabase resets SQL; clear the Mongo inventory collection too.
    Inventory::query()->delete();
});

function freshUser(string $email = 'plugin@test.local'): User
{
    return User::query()->forceCreate([
        'name' => 'Plugin User',
        'email' => $email,
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ]);
}

function pluginHeaders(string $hash, string $username): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

function pushInventoryAs(User $user, string $hash, string $username): TestResponse
{
    Sanctum::actingAs($user);

    return test()->putJson('/api/plugin/inventory', [
        'inventory' => [
            [4151, 1],
            [556, 1000],
        ],
    ], pluginHeaders($hash, $username));
}

it('rejects pushes without an auth token', function () {
    $this->putJson('/api/plugin/inventory', ['inventory' => []])->assertUnauthorized();
});

it('rejects pushes that are missing the account headers', function () {
    Sanctum::actingAs(freshUser());

    $this->withHeaders(['Accept' => 'application/json'])
        ->putJson('/api/plugin/inventory', ['inventory' => []])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['X-Account-Hash', 'X-Account-Username']);
});

it('auto-provisions an Account on first push from a new hash', function () {
    $user = freshUser();

    expect(Account::count())->toBe(0);

    pushInventoryAs($user, 'hash-aaa', 'Zlimon')->assertSuccessful();

    expect(Account::count())->toBe(1);
    $account = Account::first();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_hash)->toBe('hash-aaa');
    expect($account->username)->toBe('Zlimon');
});

it('reuses the existing Account on subsequent pushes with the same hash', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-bbb', 'Zlimon')->assertSuccessful();
    pushInventoryAs($user, 'hash-bbb', 'Zlimon')->assertSuccessful();

    expect(Account::count())->toBe(1);
});

it('records a UsernameHistory row when the supplied username drifts', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-ccc', 'OldName')->assertSuccessful();
    pushInventoryAs($user, 'hash-ccc', 'NewName')->assertSuccessful();

    $account = Account::where('account_hash', 'hash-ccc')->first();
    expect($account->username)->toBe('NewName');

    $history = UsernameHistory::query()->get();
    expect($history)->toHaveCount(1);
    expect($history->first()->old_username)->toBe('OldName');
    expect($history->first()->new_username)->toBe('NewName');
});

it('rejects with 403 when the hash is linked to a different user', function () {
    $userA = freshUser('a@test.local');
    $userB = freshUser('b@test.local');

    pushInventoryAs($userA, 'hash-ddd', 'Zlimon')->assertSuccessful();

    pushInventoryAs($userB, 'hash-ddd', 'Impostor')->assertForbidden();

    expect(Account::count())->toBe(1);
    expect(UsernameHistory::query()->count())->toBe(0);
});

it('upserts the Mongo inventory document on push', function () {
    $user = freshUser();

    pushInventoryAs($user, 'hash-eee', 'Zlimon')->assertSuccessful();

    $account = Account::where('account_hash', 'hash-eee')->first();
    $inv = Inventory::where('account_id', $account->id)->first();

    expect($inv)->not->toBeNull();
    expect($inv->inventory)->toBe([[4151, 1], [556, 1000]]);
});
