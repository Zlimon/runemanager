<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function linkHeaders(string $hash, string $username): array
{
    return [
        'Accept' => 'application/json',
        'X-Account-Hash' => $hash,
        'X-Account-Username' => $username,
    ];
}

it('claims a pre-created roster account on first plugin login', function () {
    // A roster account seeded by the owner: username only, no user, no hash.
    $account = Account::factory()->create([
        'username' => 'Rostered',
        'user_id' => null,
        'account_hash' => null,
    ]);

    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], linkHeaders('hash-rostered', 'Rostered'))
        ->assertSuccessful();

    $account->refresh();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_hash)->toBe('hash-rostered');
});

it('refuses to claim an account already owned by another user', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    Account::factory()->for($owner)->create([
        'username' => 'Taken',
        'account_hash' => null,
    ]);

    $intruder = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($intruder);

    $this->putJson('/api/plugin/heartbeat', [], linkHeaders('hash-intruder', 'Taken'))
        ->assertForbidden();
});

it('still auto-creates an account for an unrostered character', function () {
    $user = User::factory()->withPersonalTeam()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/heartbeat', [], linkHeaders('hash-new', 'Freshie'))
        ->assertSuccessful();

    $account = Account::where('username', 'Freshie')->firstOrFail();
    expect($account->user_id)->toBe($user->id);
    expect($account->account_hash)->toBe('hash-new');
});
